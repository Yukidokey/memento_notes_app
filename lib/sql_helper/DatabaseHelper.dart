import 'package:sqflite/sqflite.dart' as sql;

class DatabaseHelper {
  static Future<sql.Database> db() async {
    return sql.openDatabase(
      'memento.db', // Consolidated database
      version: 1,
      onCreate: (sql.Database database, int version) async {
        await createTables(database);
      },
    );
  }

  static Future<void> createTables(sql.Database database) async {
    await database.execute("""
      CREATE TABLE IF NOT EXISTS users(
        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      )
      """);

    await database.execute("""
      CREATE TABLE IF NOT EXISTS notes(
        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        userId INTEGER NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE
      )
      """);
  }

  // --- User Methods ---

  static Future<List<Map<String, dynamic>>> checkifUserExists(String username) async {
    final db = await DatabaseHelper.db();
    return await db.query('users', where: 'username = ?', whereArgs: [username]);
  }

  static Future<int> insertUser(String username, String password) async {
    final db = await DatabaseHelper.db();
    final data = {'username': username, 'password': password};
    return await db.insert('users', data, conflictAlgorithm: sql.ConflictAlgorithm.replace);
  }

  static Future<List<Map<String, dynamic>>> getAllUsers() async {
    final db = await DatabaseHelper.db();
    return await db.query('users', orderBy: 'username ASC');
  }

  static Future<int> updateUser(int id, String username, String password) async {
    final db = await DatabaseHelper.db();
    final data = {'username': username, 'password': password};
    return await db.update('users', data, where: 'id = ?', whereArgs: [id]);
  }

  static Future<int> deleteUser(int id) async {
    final db = await DatabaseHelper.db();
    return await db.delete('users', where: 'id = ?', whereArgs: [id]);
  }

  static Future<List<Map<String, dynamic>>> loginUser(String username, String password) async {
    final db = await DatabaseHelper.db();
    return await db.query('users', where: 'username = ? AND password = ?', whereArgs: [username, password]);
  }

  // --- Note Methods ---

  static Future<int> createNote(int userId, String title, String content) async {
    final db = await DatabaseHelper.db();
    final data = {'userId': userId, 'title': title, 'content': content};
    return await db.insert('notes', data, conflictAlgorithm: sql.ConflictAlgorithm.replace);
  }

  static Future<List<Map<String, dynamic>>> getAllNotesForUser(int userId) async {
    final db = await DatabaseHelper.db();
    return await db.query('notes', where: 'userId = ?', whereArgs: [userId], orderBy: 'createdAt DESC');
  }

  static Future<int> updateNote(int id, String title, String content) async {
    final db = await DatabaseHelper.db();
    final data = {'title': title, 'content': content, 'createdAt': DateTime.now().toString()};
    return await db.update('notes', data, where: 'id = ?', whereArgs: [id]);
  }

  static Future<void> deleteNote(int id) async {
    final db = await DatabaseHelper.db();
    try {
      await db.delete('notes', where: 'id = ?', whereArgs: [id]);
    } catch (err) {
      print("Something went wrong when deleting an item: $err");
    }
  }
}
