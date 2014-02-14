Filecharger 1.0
=======================================================================

http://filecharger.com  
For any webserver running PHP 4 and above  
By [Guangcong Luo][1]

  [1]: http://guangcongluo.com


Installation / Using the script
=======================================================================

Installation
----------------------------------

1. If you can unzip files online (e.g. with CPanel):

     Send `fileman.zip` to the directory of your webserver you want it to
     be, then extract it.

   Otherwise:

     Send the contents of `fileman.zip` to the directory of your webserver
     you want it to be.

2. Navigate to the directory you installed it in (`example.com/fileman/`)

3. Follow the on-screen instructions.

Note: If this process doesn't work, you can also set it up manually
by editing `config-example.inc.php` and then renaming it to
`config.inc.php`.


Usage
----------------------------------

Go to the directory of your webserver you installed it. For instance,
if you uploaded it to http://example.com/fileman/ , go there.


Overview
=======================================================================

Fileman is a PHP script that lets you manage your files (upload,
delete, rename, edit, etc), featuring such useful abilities as drag
selection, and drag-and-drop file uploading.

All OSes:

`Click on icon`                 Select one file/folder  
`Ctrl+click on icon`            Select multiple files/folders  
`Double-click on icon`          Open file/folder  
`click on blank space`          Deselect all  
`Double-click on blank space`   Select all  
`Arrow keys`                    Select in direction of arrow key

OS X:

`Cmd+A`                         Select All  
`Cmd+X`                         Cut (move)  
`Cmd+C`                         Copy  
`Cmd+V`                         Paste  
`Cmd+Down`                      View/Open  
`Enter`                         Rename  
`Del (Fn+Delete)`               Delete  
`Cmd+Delete`                    Delete without asking

Windows, Linux, and other OSes:

`Ctrl+A`                        Select All  
`Ctrl+X`                        Cut (move)  
`Ctrl+C`                        Copy  
`Ctrl+V`                        Paste  
`Enter`                         View/Open  
`F2`                            Rename  
`Del`                           Delete  
`Ctrl+Del`                      Delete without asking


Credits
=======================================================================

- Nathan Lapierre, for giving web hosting throughout these years.
  > novawave.ca
- Paul Klevorn, for helping test compatibility with iCab/Mac and
  Safari/Mac.
- David Capel and David Benjamin, for helping test compatibility with
  Konqueror/Linux.
- All other testers and idea-givers who wish to remain anonymous.
- PPK, for his excellent JavaScript resource QuirksMode.
  > quirksmode.org
- The PHP Group, for making PHP.

Filecharger comes with addons that make use of these libraries:

- JW FLV Media Player for FLV playback
  > http://www.longtailvideo.com/players/jw-flv-player/
- PhpConcept Library Zip for ZIP compression/extraction
  > http://www.phpconcept.net/pclzip/index.en.php
- CodeMirror for syntax highlighting
  > http://marijn.haverbeke.nl/codemirror/


FAQ
=======================================================================

Q: I forgot my admin password, what do I do?
A: Open persist.inc.php, and change the admin password to FALSE (No
   quotation marks), and delete the line that starts with `'inst_id' =>`.
   Then go to fileman/install.php and reinstall Filecharger.


License
=======================================================================

Filecharger is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Filecharger is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Filecharger.  If not, see <http://www.gnu.org/licenses/>.


History
=======================================================================

Filecharger 1.0
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






