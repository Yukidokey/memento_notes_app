# Blueprint: Memento App - Note-Taking Homepage

## Overview

This blueprint outlines the plan to transform the homepage into a functional note-taking interface for the "Memento" app. This includes creating a new database for notes, building a note creation/editing screen, and displaying all notes on the homepage.

## Design & Style

*   **Homepage:** A clean and modern grid-based layout to display notes.
*   **Note Cards:** Each note will be displayed as a card with a title, a preview of the content, and a delete button.
*   **Note Editor:** A dedicated screen for creating and editing notes with fields for a title and content.
*   **Color Palette:** Continue to use the sophisticated and calming palette of deep blues, greys, and white.
*   **Typography:** The 'Poppins' font will be used for all text elements.

## Features

### Note-Taking (CRUD)

1.  **Create Note Database:**
    *   Create a new file, `lib/sql_helper/notes_database.dart`, to handle all database operations for notes (CRUD).
        *   Define a `notes` table with `id`, `title`, and `content` columns.
        2.  **Create Note Editor Screen:**
            *   Create a new file, `lib/note_editor_screen.dart`, for creating and editing notes.
                *   The screen will include `TextField` widgets for the title and content.
                    *   A "Save" button in the `AppBar` will add or update the note in the database.
                    3.  **Redesign Homepage:**
                        *   Rewrite `lib/Homepage.dart` to be the main screen for displaying notes.
                            *   Fetch and display all notes from the database in a `GridView`.
                                *   Implement a `FloatingActionButton` to navigate to the `NoteEditorScreen` to create a new note.
                                    *   Allow users to tap on a note to open it in the `NoteEditorScreen` for editing.
                                        *   Add a delete button to each note card to remove notes.

                                        ### User Management

                                        1.  **Manage Users Button:**
                                            *   Add a "Manage Users" icon to the `AppBar` on the homepage.
                                                *   This will navigate to the `Users` screen.
                                                2.  **User CRUD Functionality:**
                                                    *   The `Users` screen will allow for creating, reading, updating, and deleting user accounts.

                                                    ### Authentication

                                                    1.  **Logout Button:**
                                                        *   Add a "Logout" icon to the `AppBar` on the homepage.
                                                            *   Tapping the button will show a confirmation dialog.
                                                                *   Upon confirmation, the user will be navigated back to the `LoginScreen`, and the navigation history will be cleared.

                                                                ## Plan

                                                                1.  **Update `Homepage.dart`:**
                                                                    *   Add a "Manage Users" and "Logout" icon to the `AppBar`.
                                                                        *   Implement the navigation to the `Users` screen.
                                                                            *   Implement the logout functionality with a confirmation dialog.
                                                                            2.  **Update `main.dart`:**
                                                                                *   Ensure the `MaterialApp` theme is consistent and that routing to the new homepage is correctly handled after login.
                                                                                