import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

// FIX: Corrected import paths to use 'memento_notes' package name
import 'package:memento_notes/dashboard_data.dart';
import 'package:memento_notes/profile.dart';

class DashboardScreen extends StatelessWidget {
  final int userId;

  const DashboardScreen({super.key, required this.userId});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        // Using GoogleFonts for consistent styling
        title: Text('Dashboard', style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            icon: const Icon(Icons.person),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => ProfilePage(userId: userId),
                ),
              );
            },
          ),
        ],
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              'Welcome to your dashboard!',
              // Using a theme style for better consistency
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 20),
            // This widget will now be correctly found
            DashboardData(userId: userId),
          ],
        ),
      ),
    );
  }
}
