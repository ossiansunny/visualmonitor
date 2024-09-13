'=============================================
'---------- main process -------------- 
Dim logMsg,basePath,plotLogPath
' get argument
logMsg = Replace(Wscript.Arguments(0),"_"," ") ' write data
basePath = Wscript.Arguments(1)                     ' "e:\visualmonitor\xampp"
plotLogPath=basePath & "\htdocs\plot\logs\"
Dim debugWsh,debug
'getDebug.vbsを実行して戻り値を受け取る。
Set debugWsh = WScript.CreateObject("WScript.Shell")
debug = debugWsh.Run (basePath & "\ubin\getDebug.vbs",,True)

If debug = 5 Then
  ' SysWriter 実行
  Dim LogFileName
  Dim yymmdd,fso,fpOut
  'ファイル名に設定する日付をyyyymmdd形式で取得します。'
  yymmdd = Right(Year(Now()), 2)
  yymmdd = yymmdd & Right("0" & Month(Now()) , 2)
  yymmdd = yymmdd & Right("0" & Day(Now()) , 2)
  'plot/logsフォルダ内にlogファイルを作成します。'
  Set objWshShell = WScript.CreateObject("WScript.Shell")
  Set fso = CreateObject("Scripting.FileSystemObject")
  LogFileName = plotLogPath & "\plot_" & yymmdd & ".log"
  'ファイルを開く、存在しない場合には作成する
  Set fpOut = fso.OpenTextFile(LogFileName, 8, true)
  fpOut.WriteLine (Date() & " " & Time() & ": " & logMsg) 'ログを書き込む
  Set fpOut = Nothing
  Set objWshShell = Nothing
End If



' 
