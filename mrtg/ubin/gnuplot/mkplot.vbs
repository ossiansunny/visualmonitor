'----���Lfunction�g�p
' vslogmake.vbs��readvar.vbs�͓����f�B���N�g��
Dim fso, path
Set fso = CreateObject("Scripting.FileSystemObject")
baseDir=fso.getParentFolderName(WScript.ScriptFullName)
path = baseDir & "\readvar.vbs"
param = "vpath_plothome,vpath_mrtgbase"
Include(path)
rtn = readvar(param)
rtnArr=Split(rtn,",")
'rtnArr(0)����
vp_plot=rtnArr(0)
vp_mrtg=rtnArr(1)
vp_plot2=Replace(vp_plot,"\","\\")
'MsgBox vp_plot2
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
'mkplot.plt����<host>.exe���쐬
host = WScript.Arguments(0)
Set fsor = WScript.CreateObject("Scripting.FileSystemObject")
Set inputFile = fsor.OpenTextFile(baseDir & "\mkplot.plt", 1, False, 0)
'Set inputFile = fsor.OpenTextFile("e:\visualmonitor\mrtg\ubin\gnuplot\mkplot.plt", 1, False, 0)

Dim fsow, outputFile, outputPath
Set fsow = WScript.CreateObject("Scripting.FileSystemObject")
outputPath = vp_plot & "\plotimage\" & host & ".exe"
Set outputFile = fsow.OpenTextFile(outputPath, 2, True, 0)
plotPath= vp_plot2 & "\\plotimage\\"
sPath = """" & plotPath & """"
'sPath = """e:\\visualmonitor\\xampp\\htdocs\\plot\\plotimage\\"""
sGhost = """" & host & """"
'MsgBox "path = " & sPath  
' ���[��""""�̐擪�ƍŌ�̓��e�����̈͂݁A����""�͂P��"�ƃG�X�P�[�v��"
outputFile.writeLine("path = " & sPath)
outputFile.WriteLine("ghost = " & sGhost)

Do Until inputFile.AtEndOfStream
  Dim lineStr, delFile
  lineStr = inputFile.ReadLine
  outputFile.WriteLine(lineStr)
Loop
