Filecharger from Novawave


-----------------------------------------------------------------------
Novawave Filecharger 1.0
http://filecharger.com
For any webserver running PHP 4 and above
By Guangcong Luo - http://novawave.ca/gluo
=======================================================================


-----------------------------------------------------------------------
| Installation / Using the script
=======================================================================

 Installation:
\----------------------------------

1. If you can unzip files online (i.e. with CPanel):

     Send fileman.zip to the directory of your webserver you want it to
     be, then extract it.

   Otherwise:

     Send the contents of fileman.zip to the directory of your webserver
     you want it to be.
     Note: Sending readme.txt or devdoc.txt is optional.

2. Navigate to the directory you installed it in (example.com/fileman/)

3. Follow the on-screen instructions.


 Usage:
\----------------------------------

 Go to the directory of your webserver you installed it. For instance,
 if you uploaded it to http://example.com/fileman/ , go there.


-----------------------------------------------------------------------
| Overview
=======================================================================

Fileman is a PHP script that lets you manage your files (upload,
delete, rename, edit, etc), featuring such useful abilities as drag
selection, and drag-and-drop file uploading.

All OSes:

Click on icon                 Select one file/folder
Ctrl+click on icon            Select multiple files/folders
Double-click on icon          Open file/folder
click on blank space          Deselect all
Double-click on blank space   Select all
Arrow keys                    Select in direction of arrow key

OS X:

Cmd+A                         Select All
Cmd+X                         Cut (move)
Cmd+C                         Copy
Cmd+V                         Paste
Cmd+Down                      View/Open
Enter                         Rename
Del (Fn+Delete)               Delete
Cmd+Delete                    Delete without asking

Windows, Linux, and other OSes:

Ctrl+A                        Select All
Ctrl+X                        Cut (move)
Ctrl+C                        Copy
Ctrl+V                        Paste
Enter                         View/Open
F2                            Rename
Del                           Delete
Ctrl+Del                      Delete without asking


-----------------------------------------------------------------------
| None of The Above
=======================================================================

Questions? Comments? Suggestions?
E-mail gluo@novawave.ca

My portfolio is...

 http://novawave.ca/gluo

My E-mail address is...

 gluo@novawave.ca

=======================================================================
Special Thanks to:

- Nathan LaPierre, for giving web hosting throughout these years.
  > novawave.ca
- Paul Klevorn, for helping test compatibility with iCab/Mac and
  Safari/Mac.
- David Capel and David Benjamin, for helping test compatibility with
  Konqueror/Linux.
- All other testers and idea-givers who wish to remain anonymous.
- PPK, for his excellent JavaScript resource QuirksMode.
  > quirksmode.org
- The PHP Group, for making PHP.

AEsoft File Manager comes with addons that make use of these libraries:

- JW FLV Media Player for FLV playback
  > http://www.longtailvideo.com/players/jw-flv-player/
- PhpConcept Library Zip for ZIP compression/extraction
  > http://www.phpconcept.net/pclzip/index.en.php
- CodeMirror for syntax highlighting
  > http://marijn.haverbeke.nl/codemirror/

-----------------------------------------------------------------------
| FAQ
=======================================================================

Q: I forgot my admin password, what do I do?
A: Open persist.inc.php, and change the admin password to FALSE (No
   quotation marks). Then go to fileman/install.php and reinstall
   File Manager.


-----------------------------------------------------------------------
| The Fine Print...
=======================================================================

I am not responsible for any damage done to your computer and/or web
server, although I can truthfully say that, to the best of my knowledge,
it is nearly impossible for this PHP script to damage your computer
and/or web server.

In short, do not blame, sue, threaten, hold for ransom, kill, or injure
me if this PHP script does not work correctly.


-----------------------------------------------------------------------
| License
=======================================================================

This software is licensed under
Creative Commons Attribution-NonCommercial-ShareAlike
http://creativecommons.org/licenses/by-nc-sa/3.0/
Unless another license has been obtained in writing from Novawave Inc.


-----------------------------------------------------------------------
| Known Bugs
=======================================================================

All know bugs have been fixed.


-----------------------------------------------------------------------
| History
=======================================================================

Novawave Filecharger 1.0
- Changed Name

AEsoft File Manager 1.0 RC1
[unreleased]
- Syntax highlighting in the text editor
- New keyboard shortcuts:
  - Arrow keys to select
  - Enter (OS X) to rename 
  - Cmd+Down (OS X) or Enter (other OSes) to open/view on other OSes
  - Cmd+C, Cmd+V, etc - equivalent of Ctrl+C, Ctrl+V, etc
  - Cmd+Delete to delete
- Drag-and-drop file uploading on Chrome, Safari, and Firefox
- Upload progress bars on Chrome, Safari, and Firefox
- File and Folder Tasks have been removed because it duplicates
  functionality from the action bar and the right-click menu

AEsoft File Manager 1.0 Beta 3
Sept 30, 2008
- ZIP files can be extracted and created
- Administrator control panel changed
- Many bugfixes, security improvements, and other minor improvements
  - Works correctly with files with ' " & % in their names
  - Files that were unsuccessfully pasted will remain in clipboard
  - Installer is better at reinstalling after Fileman has been moved
  - Installer no longer crashes on PHP 4
  - FLV player is resizable

AEsoft File Manager 1.0 Beta 2
Apr 24, 2008
- Files and folders can be copied and moved
- Files can be drag-selected
- CHMOD support
- Support for FTP
- Added an installer
- Keyboard combinations (Ctrl+C, Ctrl+V, Del, F2...)
- Admin panel and action bar greatly improved and no longer experimental
  - File and Folder Tasks has been obsoleted by the new action bar and
    is now collapsed by default
- Images can be previewed in sidebar
- Added FLV player
- Many other minor improvements and bug fixes
  - TextEdit can change the linebreak type between CR+LF, LF, CR, etc

AEsoft File Manager 1.0 Beta 1
Nov 3, 2007
- Support for multiple users
  - Experimental admin panel for editing users
- Action bar added
  - Experimental support for one-click uploading
- Many icons updated
- Right-click menu added to empty space
- Textedit backported to File Manager
- Text files word-wrap properly now

AEsoft File Manager 0.3 Beta 2
- Upload form does not jump on Firefox/Konqueror/Safari any more

AEsoft File Manager 0.3 Beta 1
- PHP files are not allowed to be uploaded, to prevent exploits
- TinyMCE support removed, because it is near impossible to use with
  frames

AEsoft File Manager 0.2 Beta
- Changed name
- Added New File and New Folder options
- Folders can now be deleted as well as files
- Added new option: Use TinyMCE, defaults to off because of Firefox bug
- Some areas (config, readme) are rewritten to be clearer






