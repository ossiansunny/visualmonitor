'=============================================
'--------------------------------------------
'----共有function使用
' vslogcheck.vbsとreadvar.vbsは同じディレクトリ
Dim fso
Set fso = CreateObject("Scripting.FileSystemObject")
baseDir=fso.getParentFolderName(WScript.ScriptFullName)
path = baseDir & "\readvar.vbs"
Include(path)
param = "vpath_plothome,vpath_mrtgbase"
rtn = readvar(param)
rtnArr=Split(rtn,",")
vp_plot=rtnArr(0)
vp_mrtg=rtnArr(1)
'Wscript.Echo vp_plot
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
' 
host = WScript.Arguments(0)
SysWriter("....." & host & " vslogcopy.vbs enter.....")
' 5日間隔でコピー
Dim w_Day
w_Day = Day(Now())

If w_Day="5" Or w_Day="10" Or w_Day="15" Or w_Day="20" Or w_Day="25" Or w_Day="30" Then
  src = vp_plot & "\plotimage\" & host & ".log"
  dst = vp_plot & "\plotimage\" & host & ".log.backup"
  Set objFS = CreateObject("Scripting.FileSystemObject")
  objFS.CopyFile src, dst, True
  SysWriter("Copied " & src & " to " & dst & "every 5 days") 
End If
SysWriter("....." & host & " vslogcopy.vbs exit.....") 
