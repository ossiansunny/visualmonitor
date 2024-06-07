Option Explicit
Dim argCount
argCount=WScript.Arguments.Count
If argCount <> 3 Then
  WScript.Echo "引数にホスト、OSタイプ、コミュニティを設定して下さい"
  WScript.Quit
End If
Dim host, ostype, comm
host=WScript.Arguments(0)
ostype=WScript.Arguments(1)
comm=WScript.Arguments(2)
Dim objShell,objExec,cpuGet,cpuArr,maxCpu,line,errSw
cpuGet=""
Set objShell = CreateObject("WScript.Shell")
If ostype = "windows" Then
  cpuGet = "snmpwalk -v1 -c" & comm & " " & host & " .1.3.6.1.2.1.25.3.3.1.2"
Else
  cpuGet = "snmpwalk -v1 -c" & comm & " " & host & " .1.3.6.1.4.1.2021.10.1.5"
End If
Set objExec = objShell.Exec(cpuGet)
maxCpu=0
errSw=0
Do While objExec.StdOut.AtEndOfStream = false
  line = objExec.Stdout.ReadLine
  cpuArr = Split(line, ": ")
  If Ubound(cpuArr) = -1 Then
    errSw=1
    Exit Do
  Else
    If cpuArr(1) > maxCpu Then
      maxCpu=cpuArr(1)
    End If
  End If
Loop
If errSw = 0 Then
  WScript.Echo maxCpu
  WScript.Echo 100
Else
  WScript.Echo 0
  WScript.Echo 100
End If
