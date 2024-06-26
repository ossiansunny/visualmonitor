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
'---------- process -------------- 
Dim debugWsh,debug,str
'getDebug.vbsを実行して戻り値を受け取る。
Set debugWsh = WScript.CreateObject("WScript.Shell")
debug = debugWsh.Run (vp_mrtg & "\ubin\gnuplot\getDebug.vbs",,True)
str = Replace(Wscript.Arguments(0),"_"," ")
If debug = 5 Then
  ' SysWriter 実行
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
End If



' 
