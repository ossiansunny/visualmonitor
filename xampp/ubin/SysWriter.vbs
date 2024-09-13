'=============================================
'---------- main process -------------- 
Dim logMsg,basePath,plotLogPath
' get argument
logMsg = Replace(Wscript.Arguments(0),"_"," ") ' write data
basePath = Wscript.Arguments(1)                     ' "e:\visualmonitor\xampp"
plotLogPath=basePath & "\htdocs\plot\logs\"
Dim debugWsh,debug
'getDebug.vbs�����s���Ė߂�l���󂯎��B
Set debugWsh = WScript.CreateObject("WScript.Shell")
debug = debugWsh.Run (basePath & "\ubin\getDebug.vbs",,True)

If debug = 5 Then
  ' SysWriter ���s
  Dim LogFileName
  Dim yymmdd,fso,fpOut
  '�t�@�C�����ɐݒ肷����t��yyyymmdd�`���Ŏ擾���܂��B'
  yymmdd = Right(Year(Now()), 2)
  yymmdd = yymmdd & Right("0" & Month(Now()) , 2)
  yymmdd = yymmdd & Right("0" & Day(Now()) , 2)
  'plot/logs�t�H���_����log�t�@�C�����쐬���܂��B'
  Set objWshShell = WScript.CreateObject("WScript.Shell")
  Set fso = CreateObject("Scripting.FileSystemObject")
  LogFileName = plotLogPath & "\plot_" & yymmdd & ".log"
  '�t�@�C�����J���A���݂��Ȃ��ꍇ�ɂ͍쐬����
  Set fpOut = fso.OpenTextFile(LogFileName, 8, true)
  fpOut.WriteLine (Date() & " " & Time() & ": " & logMsg) '���O����������
  Set fpOut = Nothing
  Set objWshShell = Nothing
End If



' 
