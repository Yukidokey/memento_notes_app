import 'package:sqflite/sqflite.dart' as sql;

class NotesDatabase {
  static Future<sql.Database> db() async {
    return sql.openDatabase(
      'notes.db',
      version: 3,
      onCreate: (sql.Database database, int version) async {
        await createTables(database);
      },
      onUpgrade: (sql.Database database, int oldVersion, int newVersion) async {
        if (oldVersion < 2) {
          await database.execute("ALTER TABLE notes ADD COLUMN userId INTEGER");
        }
        if (oldVersion < 3) {
          await database.execute("ALTER TABLE notes ADD COLUMN color INTEGER");
        }
      },
    );
  }

  static Future<void> createTables(sql.Database database) async {
    await database.execute("""
      CREATE TABLE IF NOT EXISTS notes(
        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        userId INTEGER,
        color INTEGER,
        createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      )
      """);
  }

  static Future<int> createNote(String title, String content, int userId, int color) async {
    final db = await NotesDatabase.db();
    final uniqueTitle = await _generateUniqueTitle(db, userId, title);
    final data = {'title': uniqueTitle, 'content': content, 'userId': userId, 'color': color};
    return await db.insert('notes', data, conflictAlgorithm: sql.ConflictAlgorithm.abort);
  }

  static Future<List<Map<String, dynamic>>> getNotesForUser(int userId) async {
    final db = await NotesDatabase.db();
    return await db.query('notes', where: 'userId = ?', whereArgs: [userId], orderBy: 'createdAt DESC');
  }

  static Future<int> updateNote(int id, String title, String content, int color) async {
    final db = await NotesDatabase.db();
    final data = {
      'title': title,
      'content': content,
      'color': color,
      // Keep createdAt intact; do not overwrite creation time on update
    };
    return await db.update('notes', data, where: 'id = ?', whereArgs: [id]);
  }

  static Future<void> deleteNote(int id) async {
    final db = await NotesDatabase.db();
    try {
      await db.delete('notes', where: 'id = ?', whereArgs: [id]);
    } catch (err) {
      print("Something went wrong when deleting an item: $err");
    }
  }

  // Ensures per-user note titles are unique by appending an incrementing suffix when needed
  static Future<String> _generateUniqueTitle(sql.Database db, int userId, String baseTitle) async {
    String candidate = baseTitle.isEmpty ? 'Untitled' : baseTitle;
    int counter = 1;
    while (true) {
      final existing = await db.query(
        'notes',
        columns: ['id'],
        where: 'userId = ? AND title = ?',
        whereArgs: [userId, candidate],
        limit: 1,
      );
      if (existing.isEmpty) {
        return candidate;
      }
      counter += 1;
      candidate = baseTitle.isEmpty ? 'Untitled ($counter)' : '$baseTitle ($counter)';
    }
  }
}
