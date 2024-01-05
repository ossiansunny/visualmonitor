With  CreateObject("ADODB.Connection")
Dim rs
.Open	"Driver={MySQL ODBC 8.1 Unicode Driver};" & _
	"Database=kanshi;Server=localhost;UID=kanshiadmin;PWD=kanshipass"
.Execute("CREATE TABLE mytbl (id INT,name VARCHAR(10))")
.Execute("INSERT INTO mytbl (id,name) VALUES (1,'apple')")
.Execute("INSERT INTO mytbl (id,name) VALUES (2,'banana')")
.Execute("INSERT INTO mytbl (id,name) VALUES (3,'candy')")
Set rs = .Execute("SELECT * FROM mytbl")
Do Until rs.Eof = True
    result_str = result_str & rs("id") &":"& rs("name") & VbCrLf
    rs.MoveNext
Loop
msgbox result_str
.Execute("DROP TABLE mytbl")
rs.Close
Set rs = Nothing
.Close
End With