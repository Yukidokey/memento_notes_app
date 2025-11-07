import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:firebase_auth/firebase_auth.dart'; // FIX: Added for logout functionality

// FIX: Corrected all import paths from 'myapp' to 'memento_notes'
import 'package:memento_notes/login_screen.dart';
import 'package:memento_notes/note_editor_screen.dart';
import 'package:memento_notes/profile.dart'; // Import the ProfilePage
import 'package:memento_notes/settings.dart';
import 'package:memento_notes/sql_helper/notes_database.dart';
import 'dart:convert';

class HomePage extends StatefulWidget {
  final int userId;

  const HomePage({super.key, required this.userId});

  @override
  HomePageState createState() => HomePageState();
}

class HomePageState extends State<HomePage> {
  late Future<List<Map<String, dynamic>>> _notesFuture;

  String _contentPreview(dynamic raw) {
    try {
      final content = raw?.toString() ?? '';
      if (content.isEmpty) return '';
      if (content.trim().startsWith('[')) {
        final List<dynamic> delta = jsonDecode(content);
        final StringBuffer buf = StringBuffer();
        for (final op in delta) {
          final insert = op['insert'];
          if (insert is String) buf.write(insert);
        }
        return buf.toString().trim();
      }
      return content;
    } catch (_) {
      return raw?.toString() ?? '';
    }
  }

  @override
  void initState() {
    super.initState();
    _refreshNotes();
  }

  void _refreshNotes() {
    setState(() {
      _notesFuture = NotesDatabase.getNotesForUser(widget.userId);
    });
  }

  void _deleteNote(int id) async {
    await NotesDatabase.deleteNote(id);
    // FIX: Check if the widget is still mounted before using its context.
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Note deleted')),
    );
    _refreshNotes();
  }

  // FIX: Added a proper logout method.
  Future<void> _logout() async {
    // Try to sign out from Firebase if available
    try {
      await FirebaseAuth.instance.signOut();
    } catch (e) {
      // Firebase not available, continue with logout anyway
    }
    if (!mounted) return;
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => const LoginScreen()),
          (Route<dynamic> route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('My Notes', style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
        actions: [
          // FIX: Navigate to ProfilePage, which contains other options like "Manage Users".
          IconButton(
            icon: const Icon(Icons.person),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => ProfilePage(userId: widget.userId),
                ),
              ).then((_) => _refreshNotes()); // Refresh notes when returning from profile
            },
          ),
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const SettingsPage()),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: _logout, // Use the new logout method
          ),
        ],
      ),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _notesFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          } else if (snapshot.hasError) {
            return Center(child: Text('Error: ${snapshot.error}'));
          } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(child: Text('No notes yet. Add one!'));
          } else {
            final notes = snapshot.data!;
            return GridView.builder(
              padding: const EdgeInsets.all(8.0),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 8.0,
                mainAxisSpacing: 8.0,
              ),
              itemCount: notes.length,
              itemBuilder: (context, index) {
                final note = notes[index];
                final color = Color(note['color'] ?? Colors.blue.value);
                return InkWell(
                  onTap: () async {
                    // Navigate to the editor and refresh notes after it pops
                    await Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => NoteEditorScreen(
                          userId: widget.userId,
                          note: note,
                        ),
                      ),
                    );
                    _refreshNotes();
                  },
                  child: Card(
                    color: color.withOpacity(0.8),
                    child: Padding(
                      padding: const EdgeInsets.all(12.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            note['title'],
                            style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 8.0),
                          Expanded(
                            child: Text(
                              _contentPreview(note['content']),
                              style: Theme.of(context).textTheme.bodyMedium,
                              overflow: TextOverflow.ellipsis,
                              maxLines: 5,
                            ),
                          ),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.end,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.delete),
                                onPressed: () => _deleteNote(note['id']),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            );
          }
        },
      ),
      floatingActionButton: FloatingActionButton(
        child: const Icon(Icons.add),
        onPressed: () async {
          // Navigate to the editor and refresh notes after it pops
          await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => NoteEditorScreen(
                userId: widget.userId,
              ),
            ),
          );
          _refreshNotes();
        },
      ),
    );
  }
}
