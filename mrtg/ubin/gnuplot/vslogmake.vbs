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
'SysWriter(".....vslogmake.vbs " & host & "; " & vp_plot & "; " & vp_mrtg & " Include OK .....")
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
' 正規表現のマッチングパターン
pattern = "Target.*cpu"
Set fsor = WScript.CreateObject("Scripting.FileSystemObject")
Set inputFile = fsor.OpenTextFile(vp_mrtg & "\newmrtg.cfg", 1, False, 0)

Do Until inputFile.AtEndOfStream
  Dim lineStr
  lineStr = inputFile.ReadLine
  lineArr= Split(lineStr," ")
  ' newmrtg.cfgから、ホストを抽出
  Set regEx = CreateObject("VBScript.RegExp")
  RegEx.Pattern = pattern
  If (regEx.Test(lineStr)) Then
        newHost=Replace(lineArr(0),"Target[","")
        newHost=Replace(newHost,".cpu]:","")
        Dim objSub
        Set objSub = WScript.CreateObject("WScript.Shell")
        intReturn = objSub.Run(vp_mrtg & "\ubin\gnuplot\vbsping.vbs " & newHost,,True)
        ' ping死活で応答を確認
        If intReturn = 0 Then
          ' logのバックアップ
          Dim objCpWsh
          Set objCpWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vslogcopy.vbs .....")
          callvscp = vp_mrtg & "\ubin\gnuplot\vslogcopy.vbs " & newHost
          objCpWsh.Run callvscp ,0,True
          SysWriter(".....vslogmake.vbs call end vslogcopy.vbs .....")
          ' logの行数調整
          Dim objCkWsh
          Set objCkWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vslogcheck.vbs .....")
          callvsck = vp_mrtg & "\ubin\gnuplot\vslogcheck.vbs " & newHost
          objCkWsh.Run callvsck ,0,True
          SysWriter(".....vslogmake.vbs call end vslogcheck.vbs .....")
          ' SNMPデータ取得 
          newOs=lineArr(3)
          newComm=lineArr(4)
          newComm=Replace(newComm,"`","")
          newComm=Replace(NewComm,VbCr,"")
          newComm=Replace(NewComm,VbLf,"")
          cpuValue=getSnmp("cpu",newHost,newOs,newComm)
          ramValue=getSnmp("ram",newHost,newOs,newComm)
          diskValue=getSnmp("disk",newHost,newOs,newComm)
          dayDate = Right("0" & Hour(Time()),2)
          Set fsow = WScript.CreateObject("Scripting.FileSystemObject")
          outputPath = vp_plot & "\plotimage\" & newHost & ".log"
          Set outputFile = fsow.OpenTextFile(outputPath, 8, True)
          wData = dayDate & " " & cpuValue & " " & ramValue & " " & diskValue
          outputFile.WriteLine(wData)
          outputFile.Close
          ' logの同一時刻まとめ、adファイル作成
          Dim objAdWsh
          Set objAdWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vsadmake.vbs .....")
          callvsad = vp_mrtg & "\ubin\gnuplot\vsadmake.vbs " & newHost
          objAdWsh.Run callvsad ,0,True
          SysWriter(".....vslogmake.vbs call end vsadmake.vbs .....")
          ' adファイルからokファイルを経由してsvgファイルを作成、
          Dim objOkWsh
          Set objOkWsh = WScript.CreateObject("WScript.Shell")
          SysWriter(".....vslogmake.vbs call begin vsokmake.vbs .....")
          objOkWsh.Run vp_mrtg & "\ubin\gnuplot\vsokmake.vbs" & " " & newHost,0,True
          SysWriter(".....vslogmake.vbs call end vsokmake.vbs .....")
        End If
  End If
Loop
SysWriter(".....vslogmake.vbs exit.....")

