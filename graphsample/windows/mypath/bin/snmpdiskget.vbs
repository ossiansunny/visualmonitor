Dim host, ostype, comm
host=WScript.Arguments(0)
ostype=WScript.Arguments(1)
comm=WScript.Arguments(2)

Dim objShell,diskSize,diskUsed,line,diskArr,diskArr2,diskId,userdOid,sizeOid 
Set objShell = CreateObject("WScript.Shell")
diskDscr = "snmpwalk -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.3"
Set objExec = objShell.exec(diskDscr)
line = objExec.Stdout.ReadLine
diskArr = Split(line, ": ")
If Not (Ubound(diskArr) = -1) Then
  If ostype = "windows" Then
    diskSize = "snmpget -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.5.1"
    diskUsed = "snmpget -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.6.1"
  Else
    diskDscr = "snmpwalk -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.3"
    Set objExec = objShell.exec(diskDscr)
    diskId=""
    Do While objExec.StdOut.AtEndOfStream = false
      line = objExec.Stdout.ReadLine
      diskArr = Split(line, ": ")
      If diskArr(1) = "/" Then
        diskArr2 = Split(diskArr(0), " = ")
        diskArr = Split(diskArr2(0), ".")
        diskId = diskArr(1)
        Exit Do
      End If
    
    Loop
    sizeOid=".1.3.6.1.2.1.25.2.3.1.5." & diskId
    usedOid=".1.3.6.1.2.1.25.2.3.1.6." & diskId
    diskSize = "snmpget -v1 -c" & comm & " " & host & " " & sizeOid
    diskUsed = "snmpget -v1 -c" & comm & " " & host & " " & usedOid
  End If 
  Set objExec = objShell.exec(diskSize)
  line = objExec.Stdout.ReadLine
  diskArr = Split(line, ": ")
  diskSize = diskArr(1)
  Set objExec = objShell.exec(diskUsed)
  line = objExec.Stdout.ReadLine
  diskArr = Split(line, ": ")
  diskUsed = diskArr(1)
  diskPer = int(diskUsed * 100 / diskSize)
  WScript.Echo diskPer
  WScript.Echo 100
Else
  WScript.Echo 0
  WScript.Echo 100
End If
