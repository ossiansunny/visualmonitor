Dim host, ostype, comm
host=WScript.Arguments(0)
ostype=WScript.Arguments(1)
comm=WScript.Arguments(2)

Dim objShell,ramGet,line,ramArr,ramSize,ramUsed,ramPer
Set objShell = CreateObject("WScript.Shell")
If ostype = "windows" Then
  ramSize = "snmpget -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.5.5"
  ramUsed = "snmpget -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.6.5"
Else
  ramSize = "snmpget -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.5.1"
  ramUsed = "snmpget -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.2.3.1.6.1"
End If
Set objExec = objShell.exec(ramSize)
line = objExec.Stdout.ReadLine
ramarr = Split(line, ": ")
If Not (Ubound(ramArr) = -1) Then
  ramSize = ramArr(1)
  Set objExec = objShell.exec(ramUsed)
  line = objExec.Stdout.ReadLine
  ramArr = Split(line, ": ")
  ramUsed = ramArr(1)
  ramPer = Int(ramUsed * 100 / ramSize)
  WScript.echo ramPer
  WScript.echo 100
Else
  WScript.Echo 0
  WScript.Echo 100
End If
