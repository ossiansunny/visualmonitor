'=============================================
'--------------------------------------------
'----共有function使用
' vslogmake.vbsとreadvar.vbsは同じディレクトリ
Dim fso
Set fso = CreateObject("Scripting.FileSystemObject")
baseDir=fso.getParentFolderName(WScript.ScriptFullName)
path = baseDir & "\readvar.vbs"
'path = "e:\visualmonitor\mrtg\ubin\gnuplot\readvar.vbs"
Include(path)
param = "vpath_plothome,vpath_mrtgbase"
rtn = readvar(param)
rtnArr=Split(rtn,",")
'rtnArr(0)から
vp_plot=rtnArr(0)
vp_mrtg=rtnArr(1)
'Wscript.Echo vp_plot & ": " & vp_mrtg
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

Function writeProcess(ByVal host, ByVal key, ByVal cpu, ByVal ram, ByVal disk)
  Dim fsow, outFile, outPath, wData
  Set fsow = WScript.CreateObject("Scripting.FileSystemObject")
  outPath = vp_plot & "\plotimage\" & host & ".ad"
  Set outFile = fsow.OpenTextFile(outPath, 8, True)  
  wData = key & " " & cpu & " " & ram & " " & disk
  outFile.WriteLine(wData)
  outFile.Close
  
End Function

host = WScript.Arguments(0)
SysWriter("....."& host & " vsadmake.vbs enter.....")

' delete host.ad
Dim delFilePath, objDEL
delFilePath = vp_plot & "\plotimage\" & host & ".ad"
Set objDEL = CreateObject("Scripting.FileSystemObject")
If objDEL.FileExists(delFilePath) Then
  objDEL.DeleteFile(delFilePath)
End If

Dim log, inPath, inFile, recctr
Dim host, oldKey, oldCpu, oldRam, oldDisk
Dim newKey, newCpu, newRam, newDisk

Set log = WScript.CreateObject("Scripting.FileSystemObject")
inPath = vp_plot & "\plotimage\" & host & ".log"
Set inFile = log.OpenTextFile(inPath, 1, False, 0)
oldKey = "99"
'recctr = 0

Do Until inFile.AtEndOfStream
  Dim lineArr, lineStr
  lineStr = inFile.ReadLine
  'WScript.Echo "readLine: " & lineStr
  lineArr=Split(lineStr," ")
  newKey = lineArr(0)
  newCpu = lineArr(1)
  newRam = lineArr(2)
  newDisk = lineArr(3)
  If oldKey <> newKey Then
    If oldKey = "99" Then
      'WScript.Echo "oldKey is 99"
    Else
       writeProcess host, oldKey, oldCpu, oldRam, oldDisk
       'WScript.Echo "writeProcess:" & host & " " & oldKey & " " & oldCpu & " " & oldRam & " " & oldDisk
      'recctr = recctr + 1
    End If
    oldKey = newKey
    oldCpu = newCpu
    oldRam = newRam
    oldDisk = newDisk
  Else  ' Equals
    If newCpu > oldCpu Then
      oldCpu = newCpu
    End If
    If newRam > oldRam Then
      oldRam = newRam
    End If
    If newDisk > oldDisk Then
      oldDisk = newDisk
    End If 
      
  End If
  
Loop
writeProcess host, oldKey, oldCpu, oldRam, oldDisk
'WScript.Echo "writeProcess:" & host & " " & oldKey & " " & oldCpu & " " & oldRam & " " & oldDisk
'ecctr = recctr + 1
SysWriter("....." & host & " vsadmake.vbs exit.....") 
