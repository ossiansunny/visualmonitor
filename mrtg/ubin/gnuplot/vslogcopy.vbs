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
 
Function SysWriter(str)
  Dim objWshShell
  Dim fso, fi
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
  '���݂��Ȃ��ꍇ�͍쐬����
  Set fi = fso.OpenTextFile(LogFileName, 8, true)
  fi.WriteLine (Date() & " " & Time() & ": " & str) '���O����������
  Set fi = Nothing
  Set objWshShell = Nothing
End Function

'---------- ���� --------------
host = WScript.Arguments(0)
SysWriter("....." & host & " vslogcopy.vbs enter.....")
' 5���Ԋu�ŃR�s�[
Dim w_Day
w_Day = Day(Now())
If w_Day="5" Or w_Day="10" Or w_Day="15" Or w_Day="20" Or w_Day="25" Or w_Day="30" Then
  src = vp_plot & "\plotimage\" & host & ".log"
  dst = vp_plot & "\plotimage\" & host & ".log.backup"
  Set objFS = CreateObject("Scripting.FileSystemObject")
  objFS.CopyFile src, dst, True
End If
SysWriter("....." & host & " vslogcopy.vbs exit.....") 
