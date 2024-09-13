'-----------------------------------------------------
'---------- SysWriter(basePath, logMessage)
'-----------------------------------------------------
Function SysWriter(basepath,str)
  Dim objswWsh,strmsg
  Set objswWsh = CreateObject("Wscript.Shell")
  strmsg = Replace(str," ","_")
  objswWsh.Run basepath & "\ubin\SysWriter.vbs " &  strmsg & " " & basepath ,0,True
  'objswWsh.Run basepath & "\xampp\mrtg\ubin\gnuplot\SysWriter.vbs " &  strmsg ,0,True  
End Function

'-----------------------------------------------------
' --------- Unixtimeから時刻取り出し
'------------------------------------------------------
Function unixtime2hour(ByVal uxTime)
  Dim jstTime, jstArr, jstHour,jstHrr
  jstTime = DateAdd("h",9,DateAdd("s",uxTime, DateSerial(1970,1,1)))
  jstArr= Split(jstTime," ")
  If UBound(jstArr) = 0 Then
    jstHour = "00"
  Else
    jstHrr = Split(jstArr(1),":")
    jstHour = Right("0" & jstHrr(0), 2)
  End If
  unixtime2hour=jstHour
End Function

'-----------------------------------------------------
'---------- cpu, ram, disk 配列埋め込み
'-----------------------------------------------------
Function adWrite(ByVal basePath, ByVal host, ByVal resourceType, ByRef resourceArray)
Dim fsor, inputFile
Set fsor = WScript.CreateObject("Scripting.FileSystemObject")
Set inputFile = fsor.OpenTextFile(basePath & "\htdocs\mrtg\mrtgimage\" & host & "." & resourceType & ".log", 1, False, 0)

Dim lineStr, lineArr, uTime, jstHour, oldjstHour, currVal, maxVal, wData
fsw=0
arrayLineCount=0
cnt=0
Do While cnt<320 ' break 28
   lineStr = inputFile.ReadLine
   'WScript.Echo lineStr
   lineArr= Split(lineStr," ")
   uTime=lineArr(0)
   jstHour = unixtime2hour(uTime)
   currVal=lineArr(1)

   If fsw=0 Then
     fsw=1
     maxVal=currVal
     oldjstHour=jstHour
   Else
     If Not (oldjstHour = jstHour) Then
       'WScript.StdOut.WriteLine oldjstHour & " " & maxVal & " arrayLineCount=" & arrayLineCount 'OK
       Select Case resourceType
         Case "cpu"
           resourceArray(0,arrayLineCount)=oldjstHour
           resourceArray(1,arrayLineCount)=maxVal
         Case "ram"
           resourceArray(2,arrayLineCount)=maxVal
         Case "disk"
           resourceArray(3,arrayLineCount)=maxVal
       End Select
       maxVal=0
       arrayLineCount=arrayLineCount+1
       If maxVal < currVal Then
         maxVal=currVal
       End If
       oldjstHour=jstHour
     End If
   End If
   cnt=cnt+1
Loop 
'WScript.StdOut.WriteLine oldjstHour & " " & maxVal & " arrayLineCount=" & arrayLineCount
Select Case resourceType
  Case "cpu"
    resourceArray(0,arrayLineCount)=oldjstHour
    resourceArray(1,arrayLineCount)=maxVal
    WScript.StdOut.WriteLine "hoour:" &oldjstHour & " cpu:" & maxVal & " arrayLineCount=" & arrayLineCount
  Case "ram"
    resourceArray(2,arrayLineCount)=maxVal
    WScript.StdOut.WriteLine "hour:" & oldjstHour & " ram:" & maxVal & " arrayLineCount=" & arrayLineCount
  Case "disk"
    resourceArray(3,arrayLineCount)=maxVal
    WScript.StdOut.WriteLine "hour:" & oldjstHour & " disk:" & maxVal & " arrayLineCount=" & arrayLineCount
End Select          
inputFile.Close
adWrite=arrayLineCount
End Function

'------------------------------------------------------------
'-------- リソース配列から逆順に統合ファイル作成
'------------------------------------------------------------
Function  svgMake(ByVal basePath, ByVal host, ByVal arrayLineMax, ByRef resourceArray)
  Dim fsok, okOut, arrayLineCount
  Set fsok = WScript.CreateObject("Scripting.FileSystemObject")
  okPath = basePath & "\htdocs\plot\\plotimage\" & host & ".ok"
  Set okOut = fsok.OpenTextFile(okPath, 2, True, 0)  
  arrayLineCount=arrayLineMax-1
  Do While arrayLineCount>-1
    okData = resourceArray(0,arrayLineCount) & " " & resourceArray(1,arrayLineCount) & " " & resourceArray(2,arrayLineCount) & " " & resourceArray(3,arrayLineCount)
    okOut.WriteLine(okData)  
    'WScript.StdOut.WriteLine "host: " & host & " arrayLineCount:" & arrayLineCount & " Hour:" & resourceArray(0,arrayLineCount) & " cpu:" & resourceArray(1,arrayLineCount) & " ram:" & resourceArray(2,arrayLineCount) & " disk:" & resourceArray(3,arrayLineCount)
    arrayLineCount=arrayLineCount-1
  Loop
  okOut.Close
  'WScript.StdOut.WriteLine "Host: " & host & ".ok Created"
End Function

'------------------------------------------------------------------
'------mkplot.pltから<host>.exeを作成
'------------------------------------------------------------------
Function exeMake(ByVal basePath, ByVal host) 
  Dim fsoinmk, inMk
  Set fsoinmk = WScript.CreateObject("Scripting.FileSystemObject")
  'WScript.StdOut.WriteLine basePath & "\xampp\mrtg\ubin\gnuplot\mkplot.plt"
  Set inMk = fsoinmk.OpenTextFile(basePath & "\ubin\mkplot.plt", 1, False, 0)
  Dim fsooutMk, outMk, mkPath
  Set fsooutMk = WScript.CreateObject("Scripting.FileSystemObject")
  mkPath = basePath & "\htdocs\plot\plotimage\" & host & ".exe"
  Set outMk = fsooutMk.OpenTextFile(mkPath, 2, True, 0)
  Dim plotPath, sPath, gPath, baseDir
  plotPath= basePath & "\htdocs\plot\plotimage\"
  baseDir=Replace(basePath,"\","\\")
  sPath = """" & baseDir & "\\htdocs\\plot\\plotimage\\" & """"
  sGhost = """" & host & """"
  ' 両端の""""の先頭と最後はリテラルの囲み、中の""は１個の"とエスケープの"
  outMk.writeLine("path = " & sPath)
  outMk.WriteLine("ghost = " & sGhost)
  Dim inmkLine
  Do Until inMk.AtEndOfStream
    inmkLine = inMk.ReadLine
    outMk.WriteLine(inmkLine)
  Loop
  outMk.Close
  inMk.Close
  'WScript.StdOut.WriteLine host & ".exe created"
End Function

Function graphMake(ByVal basePath, ByVal host)
'-------------------------------------------------------------------
'------ svgグラフイメージ作成
'--------------------------------------------------------------------
Dim imgPath, cmdLine, outExec
  Set ws = CreateObject("Wscript.Shell")
  gnuPath = basePath & "\gnuplot\bin\gnuplot.exe"
  imgPath = basePath & "\htdocs\plot\plotimage\" & host & ".exe"
  cmdLine = gnuPath & " " & imgPath 
  Set outExec = ws.Exec("cmd /c " & cmdLine)
  'WScript.StdOut.WriteLine host & ".svg created"
End Function

'------------------------------------------------------
'--------- メイン パス引数
'-------------------------------------------------------
Dim argCount
argCount=WScript.Arguments.Count
If argCount <> 1 Then
  WScript.Echo "引数にパスを設定して下さい"
  WScript.Quit
End If
'-------------------------------------------------------
'--------- メイン処理
'-------------------------------------------------------
Dim resourceArray(4,50)
Dim fsolog, folder, basePath
basePath=WScript.Arguments(0) '"e:\visualmonitor\xampp"
SysWriter basePath, "***plotlog*** Start plot.log ....."
set fsolog = createObject("Scripting.FileSystemObject")
set folder = fsolog.getFolder(basePath & "\htdocs\mrtg\mrtgimage")
Dim file, logArr, numArr, delCgar, fileName, hostName, firstProcessSw, arrayLineMax
'-------------------------------------------------------
'--------- log検索
oldHost=" "
arrayLineMax=0
firstProcessSw=0
for each file in folder.files
    If fsolog.GetExtensionName(file) = "log" Then
      logArr= Split(file,".")
      numArr=UBound(logArr)
      resourceType=logArr(numArr-1)
      'WScript.Echo "log: " & file & " " & resourceType
      delChar = "." & logArr(numArr-1) & ".log"
      fileName = fsolog.GetFileName(file)
      hostName = Replace(fileName,delChar,"")
      SysWriter basePath,"***plotlog*** Get host:" & hostName & " from mrtg.cfg ....."
      'WScript.Echo "host: " & hostName
      Dim irec, oldHost
      If firstProcessSw = 0 Then
        firstProcessSw = 1
        oldHost=hostName
      Else
        '      155        21
        If Not(oldHost = hostName) Then            
          '----------------------------------------------------------
          '------- 統合ログ作成
          'WScript.Echo "host: " & oldHost & " 作成完了"
          svgMake basePath, oldHost, arrayLineMax, resourceArray
          SysWriter basePath,"***plotlog*** host: " & oldHost & " Create integrated ok file in reverse order from resource array ....." 
          exeMake basePath, oldHost
          SysWriter basePath,"***plotlog*** host: " & oldHost & " Created " & oldHost & ".exe file ....."
          graphMake basePath, oldHost
          SysWriter basePath,"***plotlog*** host: " & oldHost & " Created " & oldHost & ".svg file ....."
          oldHost=hostName
        End If  
      End If
      arrayLineMax=adWrite(basePath, hostName,resourceType, resourceArray)
      SysWriter basePath,"***plotlog*** Extract and summarize " & hostName & "." & resourceType & ".log " & arrayLineMax & "lines ....."
      'WScript.Echo "host: " & hostName & " gtype: " & resourceType & " 配列埋め込み完了"
    End If    
next 
'WScript.Echo "Last host: " & oldHost & " 作成完了"
svgMake basePath, oldHost, arrayLineMax, resourceArray
SysWriter basePath,"***plotlog*** host: " & oldHost & " Create integrated ok file in reverse order from resource array ....." 
exeMake basePath, oldHost
SysWriter basePath,"***plotlog*** host: " & oldHost & " Created " & oldHost & ".exe file ....."
graphMake basePath, oldHost
SysWriter basePath,"***plotlog*** host: " & oldHost & " Created " & oldHost & ".svg file ....."
SysWriter basePath, "***plotlog*** End plot.log ....."
