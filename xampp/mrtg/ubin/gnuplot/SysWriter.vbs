'=============================================
'--------------------------------------------

'----���Lfunction�g�p
' vslogcheck.vbs��readvar.vbs�͓����f�B���N�g��
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
'---- ���Lfunction �ǂݍ���
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
'getDebug.vbs�����s���Ė߂�l���󂯎��B
Set debugWsh = WScript.CreateObject("WScript.Shell")
debug = debugWsh.Run (vp_mrtg & "\ubin\gnuplot\getDebug.vbs",,True)
str = Replace(Wscript.Arguments(0),"_"," ")
If debug = 5 Then
  ' SysWriter ���s
  Dim LogFileName
  Dim wkNow
  '�t�@�C�����ɐݒ肷����t��yyyymmdd�`���Ŏ擾���܂��B'
  wkNow = Year(Now())
  wkNow = wkNow & Right("0" & Month(Now()) , 2)
  wkNow = wkNow & Right("0" & Day(Now()) , 2)
  '�J�����g�f�B���N�g�����擾���āA�J�����g�f�B���N�g����logs�t�H���_����log�t�@�C�����쐬���܂��B'
  Set objWshShell = WScript.CreateObject("WScript.Shell")
  Set fso = CreateObject("Scripting.FileSystemObject")
  LogFileName = vp_mrtg & "\ubin\gnuplot\logs\" & wkNow & ".log"
  '�t�@�C�����J��
  '���������݂��Ȃ��ꍇ�ɂ͍쐬����
  Set fi = fso.OpenTextFile(LogFileName, 8, true)
   
  fi.WriteLine (Date() & " " & Time() & ": " & str) '���O����������
  Set fi = Nothing
  Set objWshShell = Nothing
End If



' 