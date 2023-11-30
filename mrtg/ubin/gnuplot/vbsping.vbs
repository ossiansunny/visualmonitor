Dim objWMIService       ' Windows Managements
Dim PingSet             ' Win32_PingStatusÉNÉâÉX
Dim Ping                ' ëŒè€Ping

Set objWMIService = GetObject("winmgmts:\\.")
host = Wscript.Arguments(0)
Set PingSet = objWMIService.ExecQuery ("Select * From Win32_PingStatus Where Address ='" &  host & "'")

For Each Ping In PingSet

  Select Case Ping.StatusCode
  Case 0
    checkPing = True
  Case 11010
    checkPing = False
  End Select
Next

If checkPing = True Then
  WScript.Quit(0)
Else
  WScript.Quit(1)
End If
