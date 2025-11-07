import 'dart:convert';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_quill/flutter_quill.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:memento_notes/sql_helper/notes_database.dart';

class NoteEditorScreen extends StatefulWidget {
  final Map<String, dynamic>? note;
  final int userId;

  const NoteEditorScreen({super.key, this.note, required this.userId});

  @override
  _NoteEditorScreenState createState() => _NoteEditorScreenState();
}

class _NoteEditorScreenState extends State<NoteEditorScreen> {
  final _titleController = TextEditingController();
  late QuillController _quillController;
  final FocusNode _editorFocusNode = FocusNode();
  final ScrollController _editorScrollController = ScrollController();
  int _selectedColor = Colors.blue.value;
  double _zoomLevel = 1.0;
  bool _isDrawingMode = false;
  List<DrawingPoint> _drawingPoints = [];
  Color _drawingColor = Colors.black;
  double _strokeWidth = 2.0;

  bool _isSaving = false;
  bool _hasChanges = false;
  Timer? _autosaveTimer;

  final List<Color> _colorOptions = [
    Colors.blue,
    Colors.red,
    Colors.green,
    Colors.yellow,
    Colors.purple,
    Colors.orange,
  ];

  @override
  void initState() {
    super.initState();
    _quillController = QuillController.basic();

    // Change tracking
    _titleController.addListener(_onChanged);
    _quillController.document.changes.listen((event) {
      _onChanged();
    });

    if (widget.note != null) {
      _titleController.text = widget.note!['title'];
      _selectedColor = widget.note!['color'] ?? Colors.blue.value;

      // Load content into Quill
      try {
        final content = widget.note!['content'];
        if (content != null && content.isNotEmpty) {
          try {
            // Try to parse as Delta JSON
            final List<dynamic> deltaJson = content is String
                ? (content.startsWith('[')
                      ? jsonDecode(content)
                      : [
                          {"insert": content},
                        ])
                : content;
            _quillController.document = Document.fromJson(deltaJson);
          } catch (e) {
            // If it's plain text, insert it
            _quillController.document.insert(0, content.toString());
          }
        }
      } catch (e) {
        // Fallback to plain text
        final content = widget.note!['content'] ?? '';
        _quillController.document.insert(0, content.toString());
      }
    }
  }

  @override
  void dispose() {
    _autosaveTimer?.cancel();
    _editorFocusNode.dispose();
    _editorScrollController.dispose();
    _titleController.dispose();
    _quillController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: ImageSource.gallery);

    if (image != null && mounted) {
      final index = _quillController.selection.baseOffset;
      _quillController.document.insert(index, {'image': image.path});
    }
  }

  Future<void> _pickImageFromCamera() async {
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: ImageSource.camera);

    if (image != null && mounted) {
      final index = _quillController.selection.baseOffset;
      _quillController.document.insert(index, {'image': image.path});
    }
  }

  void _showImagePicker() {
    showModalBottomSheet(
      context: context,
      builder: (context) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_library),
              title: const Text('Choose from Gallery'),
              onTap: () {
                Navigator.pop(context);
                _pickImage();
              },
            ),
            ListTile(
              leading: const Icon(Icons.camera_alt),
              title: const Text('Take a Photo'),
              onTap: () {
                Navigator.pop(context);
                _pickImageFromCamera();
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _saveNote({
    bool navigateOnSuccess = true,
    bool silent = false,
  }) async {
    if (_isSaving) return;
    final title = _titleController.text.trim();
    final hasBody = _quillController.document.toPlainText().trim().isNotEmpty;
    final delta = _quillController.document.toDelta();
    final content = jsonEncode(delta.toJson());

    if (title.isEmpty) {
      if (!silent && mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('Title cannot be empty')));
      }
      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      if (widget.note == null) {
        // Prevent creating empty notes
        if (!hasBody) {
          if (!silent && mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Add some content before saving')),
            );
          }
          return;
        }
        await NotesDatabase.createNote(
          title,
          content,
          widget.userId,
          _selectedColor,
        );
      } else {
        await NotesDatabase.updateNote(
          widget.note!['id'],
          title,
          content,
          _selectedColor,
        );
      }

      if (mounted) {
        if (!silent) {
          ScaffoldMessenger.of(
            context,
          ).showSnackBar(const SnackBar(content: Text('Saved')));
        }
        _hasChanges = false;
        if (navigateOnSuccess) {
          Navigator.of(context).pop();
        }
      }
    } catch (e) {
      if (!silent && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to save. Try again.')),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSaving = false;
        });
      }
    }
  }

  void _onChanged() {
    _hasChanges = true;
    _scheduleAutosave();
  }

  void _scheduleAutosave() {
    _autosaveTimer?.cancel();
    _autosaveTimer = Timer(const Duration(milliseconds: 1200), () {
      if (!mounted) return;
      // Only autosave when editing an existing note
      if (widget.note != null) {
        final title = _titleController.text.trim();
        if (title.isNotEmpty) {
          _saveNote(navigateOnSuccess: false, silent: true);
        }
      }
    });
  }

  void _showColorPicker(BuildContext context, bool isHighlight) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isHighlight ? 'Highlight Color' : 'Text Color'),
        content: Wrap(
          spacing: 8,
          runSpacing: 8,
          children:
              [
                Colors.black,
                Colors.white,
                Colors.red,
                Colors.blue,
                Colors.green,
                Colors.yellow,
                Colors.orange,
                Colors.purple,
                Colors.pink,
                Colors.cyan,
              ].map((color) {
                return GestureDetector(
                  onTap: () {
                    // Color formatting - feature coming soon
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Color formatting feature coming soon!'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                    Navigator.pop(context);
                  },
                  child: Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: color,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.grey),
                    ),
                  ),
                );
              }).toList(),
        ),
      ),
    );
  }

  Widget _buildZoomControls() {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        IconButton(
          icon: const Icon(Icons.zoom_out),
          onPressed: () {
            setState(() {
              if (_zoomLevel > 0.5) _zoomLevel -= 0.1;
            });
          },
          tooltip: 'Zoom Out',
        ),
        Text(
          '${(_zoomLevel * 100).toInt()}%',
          style: GoogleFonts.poppins(fontSize: 14),
        ),
        IconButton(
          icon: const Icon(Icons.zoom_in),
          onPressed: () {
            setState(() {
              if (_zoomLevel < 3.0) _zoomLevel += 0.1;
            });
          },
          tooltip: 'Zoom In',
        ),
        IconButton(
          icon: const Icon(Icons.fit_screen),
          onPressed: () {
            setState(() {
              _zoomLevel = 1.0;
            });
          },
          tooltip: 'Reset Zoom',
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () async {
        if (_hasChanges && !_isSaving) {
          final shouldLeave = await showDialog<bool>(
            context: context,
            builder: (context) => AlertDialog(
              title: const Text('Discard changes?'),
              content: const Text(
                'You have unsaved changes. Do you want to leave without saving?',
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context, false),
                  child: const Text('Cancel'),
                ),
                TextButton(
                  onPressed: () => Navigator.pop(context, true),
                  child: const Text('Discard'),
                ),
              ],
            ),
          );
          return shouldLeave ?? false;
        }
        return true;
      },
      child: Scaffold(
        appBar: AppBar(
          title: Text(
            widget.note == null ? 'New Note' : 'Edit Note',
            style: GoogleFonts.poppins(fontWeight: FontWeight.bold),
          ),
          actions: [
            _buildZoomControls(),
            IconButton(
              icon: _isSaving
                  ? const SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.save),
              onPressed: (_isSaving || _titleController.text.trim().isEmpty)
                  ? null
                  : () => _saveNote(navigateOnSuccess: true, silent: false),
              tooltip: 'Save',
            ),
          ],
        ),
        body: Column(
          children: [
            // Title Field
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: TextField(
                controller: _titleController,
                decoration: InputDecoration(
                  labelText: 'Title',
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  filled: true,
                  fillColor: Theme.of(context).colorScheme.surface,
                ),
                style: GoogleFonts.poppins(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),

            // Custom Formatting Toolbar
            Container(
              color: Theme.of(context).colorScheme.surface,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    // Bold
                    IconButton(
                      icon: const Icon(Icons.format_bold, size: 20),
                      onPressed: () {
                        _quillController.formatSelection(Attribute.bold);
                      },
                      tooltip: 'Bold',
                    ),
                    // Italic
                    IconButton(
                      icon: const Icon(Icons.format_italic, size: 20),
                      onPressed: () {
                        _quillController.formatSelection(Attribute.italic);
                      },
                      tooltip: 'Italic',
                    ),
                    // Underline
                    IconButton(
                      icon: const Icon(Icons.format_underlined, size: 20),
                      onPressed: () {
                        _quillController.formatSelection(Attribute.underline);
                      },
                      tooltip: 'Underline',
                    ),
                    const VerticalDivider(),
                    // Bullet List
                    IconButton(
                      icon: const Icon(Icons.format_list_bulleted, size: 20),
                      onPressed: () {
                        _quillController.formatSelection(Attribute.ul);
                      },
                      tooltip: 'Bullet List',
                    ),
                    // Numbered List
                    IconButton(
                      icon: const Icon(Icons.format_list_numbered, size: 20),
                      onPressed: () {
                        _quillController.formatSelection(Attribute.ol);
                      },
                      tooltip: 'Numbered List',
                    ),
                    const VerticalDivider(),
                    // Text Color
                    IconButton(
                      icon: const Icon(Icons.format_color_text, size: 20),
                      onPressed: () {
                        _showColorPicker(context, false);
                      },
                      tooltip: 'Text Color',
                    ),
                    // Highlight
                    IconButton(
                      icon: const Icon(Icons.format_color_fill, size: 20),
                      onPressed: () {
                        _showColorPicker(context, true);
                      },
                      tooltip: 'Highlight',
                    ),
                    // Image
                    IconButton(
                      icon: const Icon(Icons.image, size: 20),
                      onPressed: _showImagePicker,
                      tooltip: 'Add Image',
                    ),
                    // Drawing
                    IconButton(
                      icon: Icon(
                        _isDrawingMode ? Icons.edit : Icons.brush,
                        size: 20,
                        color: _isDrawingMode ? Colors.blue : null,
                      ),
                      onPressed: () {
                        setState(() {
                          _isDrawingMode = !_isDrawingMode;
                        });
                      },
                      tooltip: 'Drawing Mode',
                    ),
                  ],
                ),
              ),
            ),
            // Color Picker for Note Color
            Container(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Text(
                    'Note Color: ',
                    style: GoogleFonts.poppins(fontWeight: FontWeight.w500),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Wrap(
                      spacing: 8,
                      children: _colorOptions.map((color) {
                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              _selectedColor = color.value;
                            });
                          },
                          child: Container(
                            width: 40,
                            height: 40,
                            decoration: BoxDecoration(
                              color: color,
                              shape: BoxShape.circle,
                              border: Border.all(
                                color: _selectedColor == color.value
                                    ? Colors.black
                                    : Colors.grey,
                                width: _selectedColor == color.value ? 3 : 1,
                              ),
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),
            ),

            // Editor Content
            Expanded(
              child: _isDrawingMode
                  ? _buildDrawingCanvas()
                  : InteractiveViewer(
                      minScale: 0.5,
                      maxScale: 3.0,
                      scaleEnabled: true,
                      child: Transform.scale(
                        scale: _zoomLevel,
                        child: Container(
                          margin: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Theme.of(context).colorScheme.surface,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.1),
                                blurRadius: 10,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: DefaultTextStyle.merge(
                            style: GoogleFonts.poppins(
                              fontSize: 16,
                              color: Theme.of(context).colorScheme.onSurface,
                            ),
                            child: QuillEditor.basic(
                              controller: _quillController,
                            ),
                          ),
                        ),
                      ),
                    ),
            ),
            // Save status hint
            if (_isSaving)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Text(
                  'Saving...',
                  style: GoogleFonts.poppins(fontSize: 12, color: Colors.grey),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawingCanvas() {
    return GestureDetector(
      onPanStart: (details) {
        setState(() {
          _drawingPoints.add(
            DrawingPoint(
              points: [details.localPosition],
              color: _drawingColor,
              strokeWidth: _strokeWidth,
            ),
          );
        });
      },
      onPanUpdate: (details) {
        setState(() {
          _drawingPoints.last.points.add(details.localPosition);
        });
      },
      onPanEnd: (details) {
        setState(() {
          _drawingPoints.last.points.add(details.localPosition);
        });
      },
      child: Stack(
        children: [
          Container(
            color: Colors.white,
            child: CustomPaint(
              painter: DrawingPainter(_drawingPoints),
              size: Size.infinite,
            ),
          ),
          // Drawing Controls
          Positioned(
            bottom: 16,
            left: 16,
            right: 16,
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                  ),
                ],
              ),
              child: Row(
                children: [
                  // Color Picker
                  ...[
                    Colors.black,
                    Colors.red,
                    Colors.blue,
                    Colors.green,
                    Colors.yellow,
                    Colors.purple,
                  ].map((color) {
                    return GestureDetector(
                      onTap: () {
                        setState(() {
                          _drawingColor = color;
                        });
                      },
                      child: Container(
                        width: 32,
                        height: 32,
                        margin: const EdgeInsets.only(right: 8),
                        decoration: BoxDecoration(
                          color: color,
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: _drawingColor == color
                                ? Colors.black
                                : Colors.grey,
                            width: _drawingColor == color ? 2 : 1,
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                  const Spacer(),
                  // Stroke Width
                  IconButton(
                    icon: const Icon(Icons.remove),
                    onPressed: () {
                      setState(() {
                        if (_strokeWidth > 1) _strokeWidth -= 1;
                      });
                    },
                  ),
                  Text('${_strokeWidth.toInt()}px'),
                  IconButton(
                    icon: const Icon(Icons.add),
                    onPressed: () {
                      setState(() {
                        if (_strokeWidth < 20) _strokeWidth += 1;
                      });
                    },
                  ),
                  // Clear
                  IconButton(
                    icon: const Icon(Icons.clear),
                    onPressed: () {
                      setState(() {
                        _drawingPoints.clear();
                      });
                    },
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// Drawing Classes
class DrawingPoint {
  List<Offset> points;
  Color color;
  double strokeWidth;

  DrawingPoint({
    required this.points,
    required this.color,
    required this.strokeWidth,
  });
}

class DrawingPainter extends CustomPainter {
  final List<DrawingPoint> drawingPoints;

  DrawingPainter(this.drawingPoints);

  @override
  void paint(Canvas canvas, Size size) {
    for (var point in drawingPoints) {
      final paint = Paint()
        ..color = point.color
        ..strokeWidth = point.strokeWidth
        ..strokeCap = StrokeCap.round
        ..strokeJoin = StrokeJoin.round;

      for (int i = 0; i < point.points.length - 1; i++) {
        canvas.drawLine(point.points[i], point.points[i + 1], paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => true;
}
