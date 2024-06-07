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
Function adWrite(ByVal host, ByVal rType, ByRef rArray)
Dim fsor, inputFile
Set fsor = WScript.CreateObject("Scripting.FileSystemObject")
Set inputFile = fsor.OpenTextFile("mrtg\" & host & "." & rType & ".log", 1, False, 0)

Dim lineStr, lineArr, uTime, jstHour, oldjstHour, currVal, maxVal, wData
fsw=0
bcnt=0
cnt=0
Do While cnt<320 ' break 28
   lineStr = inputFile.ReadLine
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
       Select Case rType
         Case "cpu"
           rArray(0,bcnt)=oldjstHour
           rArray(1,bcnt)=maxVal
         Case "ram"
           rArray(2,bcnt)=maxVal
         Case "disk"
           rArray(3,bcnt)=maxVal
       End Select
       maxVal=0
       bcnt=bcnt+1
       If maxVal < currVal Then
         maxVal=currVal
       End If
       oldjstHour=jstHour
     End If
   End If
   cnt=cnt+1
Loop 
Select Case rType
  Case "cpu"
    rArray(0,bcnt)=oldjstHour
    rArray(1,bcnt)=maxVal
  Case "ram"
    rArray(2,bcnt)=maxVal
  Case "disk"
    rArray(3,bcnt)=maxVal
End Select          
inputFile.Close
adWrite=bcnt
End Function

'------------------------------------------------------------
'-------- リソース配列から逆順に統合ファイル作成
'------------------------------------------------------------
Function  svgMake(ByVal host, ByVal lCnt, ByRef rArray)
  Dim fsok, okOut, ncnt
  Set fsok = WScript.CreateObject("Scripting.FileSystemObject")
  okPath = "plot\" & host & ".ok"
  Set okOut = fsok.OpenTextFile(okPath, 2, True, 0)  
  ncnt=lCnt-1
  Do While ncnt>-1
    okData = rArray(0,ncnt) & " " & rArray(1,ncnt) & " " & rArray(2,ncnt) & " " & rArray(3,ncnt)
    okOut.WriteLine(okData)  
    ncnt=ncnt-1
  Loop
  okOut.Close
  WScript.Echo "Host: " & host & ".ok Created"
End Function

Function exeMake(ByVal host) 
'------------------------------------------------------------------
'------mkplot.pltから<host>.exeを作成
'------------------------------------------------------------------
  Dim fsoinmk, inMk
  Set fsoinmk = WScript.CreateObject("Scripting.FileSystemObject")
  Set inMk = fsoinmk.OpenTextFile("bin\mkplot.plt", 1, False, 0)
  Dim fsooutMk, outMk, mkPath
  Set fsooutMk = WScript.CreateObject("Scripting.FileSystemObject")
  mkPath = "plot\" & host & ".exe"
  Set outMk = fsooutMk.OpenTextFile(mkPath, 2, True, 0)
  Dim sPath, gPath
  sPath = """" & "plot\\" & """"
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
  WScript.Echo host & ".exe created"
End Function

Function graphMake(ByVal host)
'-------------------------------------------------------------------
'------ svgグラフイメージ作成
'--------------------------------------------------------------------
Dim imgPath, cmdLine, outExec
  Set ws = CreateObject("Wscript.Shell")
  imgPath = "plot\" & host & ".exe"
  cmdLine = "gnuplot " & imgPath 
  Set outExec = ws.Exec("cmd /c " & cmdLine)
  WScript.Echo host & ".svg created"
End Function

'------------------------------------------------------
'--------- メイン 処理
'-------------------------------------------------------
Dim gArray(4,50)
Dim fsolog, folder
set fsolog = createObject("Scripting.FileSystemObject")
set folder = fsolog.getFolder("mrtg")
Dim file, logArr, numArr, delCgar, fileName, hostName, gSw, lRec
'-------------------------------------------------------
'--------- log検索
oldHost=" "
lRec=0
gSw=0
for each file in folder.files
    If fsolog.GetExtensionName(file) = "log" Then
      logArr= Split(file,".")
      numArr=UBound(logArr)
      gType=logArr(numArr-1)
      delChar = "." & logArr(numArr-1) & ".log"
      fileName = fsolog.GetFileName(file)
      hostName = Replace(fileName,delChar,"")
      Dim irec, oldHost
      If gSw = 0 Then
        gSw = 1
        oldHost=hostName
      Else
        '      155        21
        If Not(oldHost = hostName) Then            
          '----------------------------------------------------------
          '------- 統合ログ作成
          WScript.Echo "host: " & oldHost & " 作成完了"
          svgMake oldHost, lRec, gArray
          exeMake oldHost
          graphMake oldHost
          oldHost=hostName
        End If  
      End If
      lRec=adWrite(hostName,gType, gArray)
      WScript.Echo "host: " & hostName & " gtype: " & gType & " 配列埋め込み完了"
    End If    
next 
WScript.Echo "Last host: " & oldHost & " 作成完了"
svgMake oldHost, lRec, gArray
exeMake oldHost
graphMake oldHost

