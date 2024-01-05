With  CreateObject("ADODB.Connection")
Dim rs, debug
.Open	"Driver={MySQL ODBC 8.1 Unicode Driver};" & _
	"Database=kanshi;Server=localhost;UID=kanshiadmin;PWD=kanshipass"
Set rs = .Execute("SELECT * FROM admintb")
Do Until rs.Eof = True
    debug = rs("debug")
    rs.MoveNext
Loop
'Wscript.Echo result_str
rs.Close
Set rs = Nothing
.Close
End With
Wscript.Quit(debug)
