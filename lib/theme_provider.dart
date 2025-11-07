import 'package:flutter/material.dart';

class ThemeProvider with ChangeNotifier {
  ThemeMode _themeMode = ThemeMode.system;

  ThemeMode get themeMode => _themeMode;

  set themeMode(ThemeMode themeMode) {
    _themeMode = themeMode;
    notifyListeners();
  }

  final lightTheme = ThemeData(
    brightness: Brightness.light,
    primarySwatch: Colors.deepPurple,
    visualDensity: VisualDensity.adaptivePlatformDensity,
  );

  final darkTheme = ThemeData(
    brightness: Brightness.dark,
    primarySwatch: Colors.deepPurple,
    visualDensity: VisualDensity.adaptivePlatformDensity,
  );

  final mementoTheme = ThemeData(
    brightness: Brightness.light,
    primaryColor: const Color(0xFF2E3B4E),
    scaffoldBackgroundColor: const Color(0xFFF2F2F2),
    colorScheme: const ColorScheme.light(
      primary: Color(0xFF2E3B4E),
      secondary: Color(0xFF536DFE),
    ),
    visualDensity: VisualDensity.adaptivePlatformDensity,
  );
}
