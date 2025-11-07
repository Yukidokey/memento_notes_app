import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:awesome_dialog/awesome_dialog.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:google_sign_in/google_sign_in.dart';

// FIX 1: Corrected all import paths to use 'memento_notes'
import 'package:memento_notes/sql_helper/DatabaseHelper.dart';
import 'package:memento_notes/signup_screen.dart';
import 'package:memento_notes/Homepage.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  // FIX 4: Added loading state and standardized variable names
  bool _isLoading = false;
  bool _isGoogleLoading = false;
  bool _hidePassword = true;

  // FIX 3: Changed username to email for Firebase compatibility
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  
  final GoogleSignIn _googleSignIn = GoogleSignIn();

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _togglePasswordVisibility() {
    setState(() {
      _hidePassword = !_hidePassword;
    });
  }

  // FIX 5: Merged all logic into a single, robust async method
  Future<void> _login() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    // --- Input Validation ---
    if (email.isEmpty) {
      AwesomeDialog(context: context, dialogType: DialogType.error, title: 'Error', desc: 'Email is required!').show();
      return;
    }
    if (password.isEmpty) {
      AwesomeDialog(context: context, dialogType: DialogType.error, title: 'Error', desc: 'Password is required!').show();
      return;
    }

    // --- Start Loading State ---
    setState(() {
      _isLoading = true;
    });

    try {
      // Try Firebase authentication if available, otherwise use local database only
      try {
        await FirebaseAuth.instance.signInWithEmailAndPassword(
          email: email,
          password: password,
        );
      } on FirebaseAuthException catch (e) {
        // Firebase is available but authentication failed
        String errorMessage = 'Invalid email or password provided!';
        if (e.code == 'invalid-email') {
          errorMessage = 'The email address format is not valid.';
        }
        if (!mounted) return;
        AwesomeDialog(context: context, title: 'Login Failed', desc: errorMessage, dialogType: DialogType.error).show();
        return;
      } catch (e) {
        // Firebase is not configured or available, use local database only
        // Continue with local database authentication
      }

      // Get user from local database (works with or without Firebase)
      final loginResult = await DatabaseHelper.loginUser(email, password);
      if (!mounted) return; // Check if widget is still in the tree

      if (loginResult.isNotEmpty) {
        final user = loginResult.first;
        Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (BuildContext context) => HomePage(userId: user['id'])));
      } else {
        // User not found in local database
        if (!mounted) return;
        AwesomeDialog(
          context: context,
          title: 'Error',
          desc: 'Invalid email or password. Please check your credentials or sign up.',
          dialogType: DialogType.error,
        ).show();
      }
    } catch (e) {
      // Handle any other unexpected errors
      if (!mounted) return;
      AwesomeDialog(context: context, title: 'Error', desc: 'An unexpected error occurred: ${e.toString()}', dialogType: DialogType.error).show();
    } finally {
      // --- End Loading State ---
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  // Google Sign-In method
  Future<void> _signInWithGoogle() async {
    setState(() {
      _isGoogleLoading = true;
    });

    try {
      // Try Google Sign-In with Firebase if available
      GoogleSignInAccount? googleUser;
      try {
        googleUser = await _googleSignIn.signIn();
        if (googleUser == null) {
          // User cancelled the sign-in
          if (!mounted) return;
          setState(() {
            _isGoogleLoading = false;
          });
          return;
        }

        // Get authentication details from the request
        final GoogleSignInAuthentication googleAuth = await googleUser.authentication;

        // Try to sign in with Firebase if available
        try {
          final credential = GoogleAuthProvider.credential(
            accessToken: googleAuth.accessToken,
            idToken: googleAuth.idToken,
          );

          await FirebaseAuth.instance.signInWithCredential(credential);
        } catch (e) {
          // Firebase not available, continue with local database
        }
      } catch (e) {
        // Google Sign-In failed
        if (!mounted) return;
        AwesomeDialog(
          context: context,
          title: 'Error',
          desc: 'Google Sign-In failed. Please try again.',
          dialogType: DialogType.error,
        ).show();
        setState(() {
          _isGoogleLoading = false;
        });
        return;
      }

      // At this point, googleUser is not null
      final email = googleUser.email;

      // Check if user exists in local database
      final existingUser = await DatabaseHelper.checkifUserExists(email);
      
      int userId;
      if (existingUser.isEmpty) {
        // Create new user in local database with a default password
        // For Google users, we'll use a placeholder password since they authenticate via Google
        final userIdResult = await DatabaseHelper.insertUser(
          email,
          'google_auth_${googleUser.id}', // Store a unique identifier instead of password
        );
        userId = userIdResult;
      } else {
        userId = existingUser.first['id'];
      }

      if (!mounted) return;

      // Navigate to home page
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (context) => HomePage(userId: userId),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      AwesomeDialog(
        context: context,
        title: 'Error',
        desc: 'An error occurred during Google Sign-In: ${e.toString()}',
        dialogType: DialogType.error,
      ).show();
    } finally {
      if (mounted) {
        setState(() {
          _isGoogleLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[200],
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Card(
            elevation: 8.0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16.0)),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 40.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.note_alt_rounded, size: 80, color: Colors.deepPurple),
                  const SizedBox(height: 16.0),
                  Text('Memento', style: GoogleFonts.poppins(fontSize: 32, fontWeight: FontWeight.w600, color: Colors.deepPurple)),
                  const SizedBox(height: 8.0),
                  Text('Your Digital Notebook', style: GoogleFonts.poppins(fontSize: 16, color: Colors.grey[600])),
                  const SizedBox(height: 32.0),

                  // FIX 3: Changed TextField to accept email instead of username
                  TextField(
                    controller: _emailController,
                    decoration: InputDecoration(
                      labelText: 'Email',
                      prefixIcon: const Icon(Icons.email),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12.0)),
                    ),
                    style: GoogleFonts.poppins(),
                    keyboardType: TextInputType.emailAddress,
                  ),
                  const SizedBox(height: 16.0),

                  TextField(
                    controller: _passwordController,
                    obscureText: _hidePassword,
                    decoration: InputDecoration(
                      labelText: 'Password',
                      prefixIcon: const Icon(Icons.lock_outline_rounded),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12.0)),
                      suffixIcon: IconButton(
                        onPressed: _togglePasswordVisibility,
                        icon: Icon(_hidePassword ? Icons.visibility_off_rounded : Icons.visibility_rounded),
                      ),
                    ),
                    style: GoogleFonts.poppins(),
                  ),
                  const SizedBox(height: 32.0),

                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      // FIX 4: Show loading indicator or call login function
                      onPressed: _isLoading ? null : _login,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.deepPurple,
                        padding: const EdgeInsets.symmetric(vertical: 16.0),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12.0)),
                      ),
                      child: _isLoading
                          ? const SizedBox(height: 24, width: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 3))
                          : Text('Login', style: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.w500, color: Colors.white)),
                    ),
                  ),
                  const SizedBox(height: 24.0),

                  // Divider with "OR"
                  Row(
                    children: [
                      Expanded(child: Divider(color: Colors.grey[400], thickness: 1)),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16.0),
                        child: Text(
                          'OR',
                          style: GoogleFonts.poppins(
                            color: Colors.grey[600],
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                      Expanded(child: Divider(color: Colors.grey[400], thickness: 1)),
                    ],
                  ),
                  const SizedBox(height: 24.0),

                  // Google Sign-In Button
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton(
                      onPressed: _isGoogleLoading || _isLoading ? null : _signInWithGoogle,
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16.0),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12.0),
                          side: BorderSide(color: Colors.grey[300]!, width: 1.5),
                        ),
                        backgroundColor: Colors.white,
                      ),
                      child: _isGoogleLoading
                          ? const SizedBox(
                              height: 24,
                              width: 24,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                // Google "G" icon
                                Container(
                                  width: 24,
                                  height: 24,
                                  decoration: BoxDecoration(
                                    image: DecorationImage(
                                      image: AssetImage('assets/images/google_logo.png'),
                                      onError: (exception, stackTrace) => null,
                                    ),
                                  ),
                                  child: Image.asset(
                                    'assets/images/google_logo.png',
                                    width: 24,
                                    height: 24,
                                    errorBuilder: (context, error, stackTrace) {
                                      // Custom Google "G" icon as fallback
                                      return Container(
                                        width: 24,
                                        height: 24,
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(4),
                                          border: Border.all(
                                            color: Colors.grey[400]!,
                                            width: 1,
                                          ),
                                        ),
                                        child: Center(
                                          child: Text(
                                            'G',
                                            style: GoogleFonts.poppins(
                                              fontSize: 16,
                                              fontWeight: FontWeight.bold,
                                              color: Colors.blue[700],
                                            ),
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Text(
                                  'Continue with Google',
                                  style: GoogleFonts.poppins(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w500,
                                    color: Colors.grey[800],
                                  ),
                                ),
                              ],
                            ),
                    ),
                  ),
                  const SizedBox(height: 16.0),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('Don\'t have an account?', style: GoogleFonts.poppins()),
                      TextButton(
                        onPressed: () => Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (context) => const SignupScreen())),
                        child: Text('Sign Up', style: GoogleFonts.poppins(color: Colors.deepPurple, fontWeight: FontWeight.w600)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
