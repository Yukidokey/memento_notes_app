import 'package:flutter/material.dart';
// FIX: Corrected the import path to match your project's package name.
import 'package:memento_notes/sql_helper/notes_database.dart';

class DashboardData extends StatefulWidget {
  final int userId;

  const DashboardData({super.key, required this.userId});

  @override
  State<DashboardData> createState() => _DashboardDataState();
}

class _DashboardDataState extends State<DashboardData> {
  int _noteCount = 0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadNoteCount();
  }

  @override
  void didUpdateWidget(covariant DashboardData oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.userId != oldWidget.userId) {
      _loadNoteCount();
    }
  }

  Future<void> _loadNoteCount() async {
    // Set loading state at the beginning.
    setState(() {
      _isLoading = true;
    });

    try {
      final notes = await NotesDatabase.getNotesForUser(widget.userId);
      // Check if the widget is still in the tree before updating state.
      if (mounted) {
        setState(() {
          _noteCount = notes.length;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        // Show a snackbar on error for better user feedback.
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to load note count: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const CircularProgressIndicator();
    }
    return Text(
      'You have $_noteCount notes.',
      style: Theme.of(context).textTheme.headlineSmall,
    );
  }
}
