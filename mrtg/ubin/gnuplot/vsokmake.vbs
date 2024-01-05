'=============================================
'--------------------------------------------
'----共有function使用
' vslogmake.vbsとreadvar.vbsは同じディレクトリ
Dim fso
Set fso = CreateObject("Scripting.FileSystemObject")
baseDir=fso.getParentFolderName(WScript.ScriptFullName)
path = baseDir & "\readvar.vbs"
'path = "e:\visualmonitor\mrtg\ubin\gnuplot\readvar.vbs"
Include(path)
param = "vpath_plothome,vpath_mrtgbase"
rtn = readvar(param)
rtnArr=Split(rtn,",")
'rtnArr(0)から
vp_plot=rtnArr(0)
vp_mrtg=rtnArr(1)
'Wscript.Echo vp_plot & ": " & vp_mrtg
'--------------------------------------------
'---- 共有function 読み込み
Function Include(strFile)
  Dim objFso, objWsh, strPath
  Set objFso = Wscript.CreateObject("Scripting.FileSystemObject")
  Set objWsh = objFso.OpenTextFile(strFile)
  ExecuteGlobal objWsh.ReadAll()
  objWsh.Close
  Set objWsh = Nothing
  Set objFso = Nothing
End Function
'--------------------------------------------
'=============================================
 
Function SysWriter(str)
  Dim objswWsh,strmsg
  Set objswWsh = CreateObject("Wscript.Shell")
  strmsg = Replace(str," ","_")
  objswWsh.Run vp_mrtg & "\ubin\gnuplot\SysWriter.vbs " &  strmsg ,,True  
End Function

'---------- process --------------
'Option Explicit
'WScript.Echo "Enter into vsokmake.vbs"
' Input <host>.ad
Dim ck, ad, ckPath, inPath, inFile, lineStr, ckFile, host
host = WScript.Arguments(0)
SysWriter("....." & host & " vsokmake.vbs enter.....")
' adの行数取得
ckPath = vp_plot & "\plotimage\" & host & ".ad"
recnum = 0
With CreateObject("Scripting.FileSystemObject")
  Set fp = .OpenTextFile(ckPath)
  'SysWriter("Open " & ckPath)
  Do While fp.AtEndOfStream <> True
    recnum = recnum + 1
    fp.Readline
  Loop
  fp.Close
End With


'WScript.Echo recnum
Set ad = WScript.CreateObject("Scripting.FileSystemObject")
inPath = vp_plot & "\plotimage\" & host & ".ad"
Set inFile = ad.OpenTextFile(inPath, 1, False, 0)
' Output <host>.ok
Dim ok, outFile, outPath, flag, recnum, maxrec, skiprec
Set ok = WScript.CreateObject("Scripting.FileSystemObject")
outPath = vp_plot & "\plotimage\" & host & ".ok"
If ok.FileExists(outPath) Then
  ok.DeleteFile outPath
End If
Set outFile = ok.OpenTextFile(outPath, 8, True)  ' 8,True ... posbile add write when not found

maxrec = 36

flag = 0
If recnum > maxrec Then
  skiprec = recnum - maxrec
  Do Until inFile.AtEndOfStream
    lineStr = inFile.ReadLine
    If flag = 0 Then
      skiprec = skiprec - 1
      If skiprec = 0 Then
        flag = 1
      End If 
    Else
      outFile.WriteLine(lineStr)
    End If      
  Loop
  outFile.Close
  inFile.Close
Else
  Do Until inFile.AtEndOfStream
    lineStr = inFile.ReadLine
    outFile.WriteLine(lineStr)
  Loop
End If
SysWriter("Wrote " & maxrec & "line to " & outPath) 
' call mkplot.vbs
Dim objPlWsh
Set objPlWsh = WScript.CreateObject("WScript.Shell")
SysWriter(".....vsokmake.vbs call begin mkplot.vbs .....")
callmkPl = vp_mrtg & "\ubin\gnuplot\mkplot.vbs " & host
'MsgBox callmkPl 
objPlWsh.Run callmkPl ,0,True
  SysWriter(".....vsokmake.vbs call end mkplot.vbs .....")
' call <host>.plt
Set ws = CreateObject("Wscript.Shell")
cmdLine="gnuplot " & vp_plot & "\plotimage\" & host & ".exe"
Set outExec = ws.Exec("cmd /c " & cmdLine)
SysWriter(cmdLine)
SysWriter("....." & host & " plot success.....") 
'WScript.Echo "Return to Mail"
SysWriter("....." & host & " vsokmake.vbs exit.....") 
