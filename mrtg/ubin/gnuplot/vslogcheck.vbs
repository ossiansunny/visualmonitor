'=============================================
'--------------------------------------------
'----共有function使用
' vslogcheck.vbsとreadvar.vbsは同じディレクトリ
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
  Dim objWshShell
  Dim fso, fi
  Dim LogFileName
  Dim wkNow
  'ファイル名に設定する日付をyyyymmdd形式で取得します。'
  wkNow = Year(Now())
  wkNow = wkNow & Right("0" & Month(Now()) , 2)
  wkNow = wkNow & Right("0" & Day(Now()) , 2)
  'カレントディレクトリを取得して、カレントディレクトリのlogsフォルダ内にlogファイルを作成します。'
  Set objWshShell = WScript.CreateObject("WScript.Shell")
  Set fso = CreateObject("Scripting.FileSystemObject")
  LogFileName = vp_mrtg & "\ubin\gnuplot\logs\" & wkNow & ".log"
  'ファイルを開く
  'もしも存在しない場合には作成する
  Set fi = fso.OpenTextFile(LogFileName, 8, true)
   
  fi.WriteLine (Date() & " " & Time() & ": " & str) 'ログを書き込む
  Set fi = Nothing
  Set objWshShell = Nothing
End Function

'---------- process --------------
'Option Explicit
'WScript.Echo "Enter into vslogcheck.vbs"
' Input <host>.log
Dim ck, ad, ckPath, inPath, inFile, lineStr, ckFile, host
'host = "192.168.1.8"
host = WScript.Arguments(0)
SysWriter("....." & host & " vslogcheck.vbs enter.....")

' logの行数取得
ckPath = vp_plot & "\plotimage\" & host & ".log"
recnum = 0
With CreateObject("Scripting.FileSystemObject")
  Set fp = .OpenTextFile(ckPath)
  Do While fp.AtEndOfStream <> True
    recnum = recnum + 1
    fp.Readline
  Loop
  fp.Close
End With
'WScript.Echo recnum


'WScript.Echo recnum
Set ad = WScript.CreateObject("Scripting.FileSystemObject")
inPath = vp_plot & "\plotimage\" & host & ".log"
Set inFile = ad.OpenTextFile(inPath, 1, False, 0)
' Output <host>.logx
Dim ok, outFile, outPath, flag, recnum, maxrec, skiprec
Set ok = WScript.CreateObject("Scripting.FileSystemObject")
outPath = vp_plot & "\plotimage\" & host & ".logx"
If ok.FileExists(outPath) Then
  ok.DeleteFile outPath
End If
Set outFile = ok.OpenTextFile(outPath, 8, True)  ' 8,True ... posbile add write when not found

maxrec = 36 * 6

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
Else
  Do Until inFile.AtEndOfStream
    lineStr = inFile.ReadLine
    outFile.WriteLine(lineStr)
  Loop
End If
outFile.Close
inFile.Close
'========================================================================
'========logファイルを削除、logokファイルをokへ名前変更==================
'========================================================================
Dim mlog, logPath, logokPath, mfso
Set mlog = WScript.CreateObject("Scripting.FileSystemObject")
logPath = vp_plot & "\plotimage\" & host & ".log"
If mlog.FileExists(logPath) Then
  mlog.DeleteFile logPath
End If

logokPath = vp_plot & "\plotimage\" & host & ".logx"
Set mfso = WScript.CreateObject("Scripting.FileSystemObject")
mfso.MoveFile logokPath, logPath
SysWriter("....." & host & " vslogcheck.vbs exit.....") 
