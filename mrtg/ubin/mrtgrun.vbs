Function SysWriter(basedir,str)
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
  LogFileName = basedir & "\ubin\gnuplot\logs\" & wkNow & ".log"
  'ファイルを開く
  'もしも存在しない場合には作成する
  Set fi = fso.OpenTextFile(LogFileName, 8, true)
  fi.WriteLine (Date() & " " & Time() & ": " & str) 'ログを書き込む
  Set fi = Nothing
  Set objWshShell = Nothing
End Function
argcount=WScript.Arguments.Count
If argcount <> 1 Then
  WScript.Echo "引数エラー"
  Wscript.Quit
End If
mrtgbase=WScript.Arguments(0)
'WScript.Echo mrtgbase
copyorg=mrtgbase & "\newmrtg.cfg"
copydst=mrtgbase & "\copymrtg.cfg"
mrtg=mrtgbase & "\bin\mrtg"
perlcmd="cmd /c perl " & mrtg & " " & copydst 
'WScript.Echo perlcmd
Set fs = WScript.CreateObject("Scripting.FileSystemObject")
fs.CopyFile copyorg,copydst
Set ws = CreateObject("Wscript.Shell")
'ws.run "cmd /c ""perl e:\mrtg\bin\mrtg e:\mrtg\copymrtg.cfg""", 0,True
ws.run perlcmd,0
'ws.run "cmd /c ""perl " & mrtg & " " & copydst""",0,True
param = ".....done mrtgrun.vbs with copied copymrtg.cfg....."
SysWriter mrtgbase,param
