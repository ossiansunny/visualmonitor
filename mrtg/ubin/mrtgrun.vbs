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
  '存在しない場合は作成する
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
copyorg=mrtgbase & "\newmrtg.cfg"
copydst=mrtgbase & "\copymrtg.cfg"
mrtg=mrtgbase & "\bin\mrtg"
perlcmd="cmd /c perl " & mrtg & " " & copydst 
Set fs = WScript.CreateObject("Scripting.FileSystemObject")
fs.CopyFile copyorg,copydst
Set ws = CreateObject("Wscript.Shell")
ws.run perlcmd,0
param = ".....done mrtgrun.vbs with copied copymrtg.cfg....."
SysWriter mrtgbase,param
