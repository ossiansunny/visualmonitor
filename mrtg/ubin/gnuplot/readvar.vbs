Dim fso
Set fso = CreateObject("Scripting.FileSystemObject")
curDir=fso.getParentFolderName(WScript.ScriptFullName)
sPath = Split(curDir,"mrtg")
kanshiphpDir = sPath(0) & "xampp\htdocs\kanshiphp\vmsetup"

Function readvar(rv_arg)
Dim rv_argarr,rv_argcnt,rv_fsor,rv_kanshiphp,rv_inputFile
rv_argarr = split(rv_arg,",")
rv_argcnt = Ubound(rv_argarr)
Set rv_fsor = WScript.CreateObject("Scripting.FileSystemObject")
rv_kanshiphp = kanshiphpDir
Set rv_inputFile = rv_fsor.OpenTextFile(rv_kanshiphp & "\kanshiphp.ini", 1, False, 0)

Dim rv_rtnVal
rv_rtnVal = ""
Do Until rv_inputFile.AtEndOfStream
  
  rv_lineStr = rv_inputFile.ReadLine
  'Wscript.Echo "lineStr: " & rv_lineStr
  rv_item = split(rv_lineStr,"=")
  rv_key = Trim(rv_item(0))
  rv_value = Trim(rv_item(1))
  rv_value = Replace(rv_value,"""","")
  For i = 0 to rv_argcnt  
    If rv_argarr(i) = rv_key Then
      rv_rtnVal = rv_rtnVal & "," & rv_value
      
    End If
  Next
Loop
rv_inputFile.Close
rv_rtnVal=Right(rv_rtnVal,Len(rv_rtnVal)-1)
readvar = rv_rtnVal
End Function


'param = "vpath_plothome,vpath_mrtgbase"
'rtn = readvar(param)
'rtnArr=Split(rtn,",")
'rtnArr(0)‚©‚ç
'Wscript.Echo rtnArr(0) & ": " & rtnArr(1)

