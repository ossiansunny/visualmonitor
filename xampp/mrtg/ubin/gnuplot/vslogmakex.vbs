'=============================================
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
SysWriter("PATH " & vp_plot & "; " & vp_mrtg)
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
  Dim objswWsh,strmsg
  Set objswWsh = CreateObject("Wscript.Shell")
  strmsg = Replace(str," ","_")
  objswWsh.Run "SysWriter.vbs " &  strmsg ,,True  
End Function

Function getSnmp(ByVal getType,ByVal getHost,ByVal getOs,ByVal getComm)
  Dim ws, cmdLine, outExec, outStream, strOut, fSw
  Set ws = CreateObject("Wscript.Shell")
  cmdLine=vp_mrtg & "\ubin\snmp" & getType & "get " & getHost & " " & getOs & " " & getComm
  Set outExec = ws.Exec("cmd /c " & cmdLine)
  Set outStream = outExec.StdOut
  fSw=0
  Do While outStream.AtEndOfStream <> True
      strOut = outStream.ReadLine
      If fSw = 0 Then
          rtnOut=strOut
          fSw=1
      End If
  Loop 
  outStream.Close
  getSnmp=rtnOut
End Function
SysWriter(".....vslogmake.vbs enter.....")
' ���K�\���̃}�b�`���O�p�^�[��
pattern = "Target.*cpu"
Set fsor = WScript.CreateObject("Scripting.FileSystemObject")
Set inputFile = fsor.OpenTextFile(vp_mrtg & "\newmrtg.cfg", 1, False, 0)
SysWriter("Open " & vp_mrtg & "\newmrtg.cfg")
' newmrtg.cfg��ǂݎ��
Do Until inputFile.AtEndOfStream
  Dim lineStr
  lineStr = inputFile.ReadLine
  lineArr= Split(lineStr," ")
  ' �z�X�g�𒊏o
  Set regEx = CreateObject("VBScript.RegExp")
  RegEx.Pattern = pattern
  If (regEx.Test(lineStr)) Then
        newHost=Replace(lineArr(0),"Target[","")
        newHost=Replace(newHost,".cpu]:","")
        ' <host>.log��������΍쐬
        Dim objLog
        Set objLog = WScript.CreateObject("Scripting.FileSystemObject")
        hostLog = vp_plot & "\plotimage\" & newHost & ".log"
        If Not objLog.FileExists(hostLog) Then
          objLog.CreateTextFile(hostLog)
          Set objLog = Nothing
        End If
        Dim objSub
        Set objSub = WScript.CreateObject("WScript.Shell")
        intReturn = objSub.Run(vp_mrtg & "\ubin\gnuplot\vbsping.vbs " & newHost,,True)
        ' ping�����ŉ������m�F
        If intReturn = 0 Then
          ' log�̃o�b�N�A�b�v
          Dim objCpWsh
          Set objCpWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vslogcopy.vbs .....")
          callvscp = vp_mrtg & "\ubin\gnuplot\vslogcopy.vbs " & newHost
          objCpWsh.Run callvscp ,0,True
          SysWriter(".....vslogmake.vbs call end vslogcopy.vbs .....")
          ' log�̍s�������A
          Dim objCkWsh
          Set objCkWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vslogcheck.vbs .....")
          callvsck = vp_mrtg & "\ubin\gnuplot\vslogcheck.vbs " & newHost
          objCkWsh.Run callvsck ,0,True
          SysWriter(".....vslogmake.vbs call end vslogcheck.vbs .....")
          ' SNMP�f�[�^�擾 
          newOs=lineArr(3)
          newComm=lineArr(4)
          newComm=Replace(newComm,"`","")
          newComm=Replace(NewComm,VbCr,"")
          newComm=Replace(NewComm,VbLf,"")
          cpuValue=getSnmp("cpu",newHost,newOs,newComm)
          'SysWriter("Get snmp Cpu data " & newHost & " " & newOs & " " & newComm)
          ramValue=getSnmp("ram",newHost,newOs,newComm)
          'SysWriter("Get snmp Ram data " & newHost & " " & newOs & " " & newComm)
          diskValue=getSnmp("disk",newHost,newOs,newComm)
          SysWriter("Got snmp data " & newHost & " " & newOs & " " & newComm)
          dayDate = Right("0" & Hour(Time()),2)
          Set fsow = WScript.CreateObject("Scripting.FileSystemObject")
          outputPath = vp_plot & "\plotimage\" & newHost & ".log"
          Set outputFile = fsow.OpenTextFile(outputPath, 8, True)
          wData = dayDate & " " & cpuValue & " " & ramValue & " " & diskValue
          outputFile.WriteLine(wData)
          outputFile.Close
          SysWriter("Wrote snmp data " & wData & " to " & outputPath)
          ' log�̓��ꎞ���܂Ƃ߁Aad�t�@�C���쐬
          Dim objAdWsh
          Set objAdWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vsadmake.vbs .....")
          callvsad = vp_mrtg & "\ubin\gnuplot\vsadmake.vbs " & newHost
          objAdWsh.Run callvsad ,0,True
            SysWriter(".....vslogmake.vbs call end vsadmake.vbs .....")
          ' ad�t�@�C������ok�t�@�C�����o�R����svg�t�@�C�����쐬�A
          Dim objOkWsh
          Set objOkWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vsokmake.vbs .....")
          objOkWsh.Run vp_mrtg & "\ubin\gnuplot\vsokmake.vbs" & " " & newHost,0,True
          SysWriter(".....vslogmake.vbs call end vsokmake.vbs .....")
        End If
  End If
Loop
SysWriter(".....vslogmake.vbs exit.....")
