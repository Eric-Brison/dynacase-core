Dim i
Dim Thepath
Dim Tab
Dim oRegExp


Set oRegExp=New RegExp
'MsgBox(Wscript.Arguments(1))
if (Wscript.Arguments(0) = 1) then
  OpenAs = 1
else
  OpenAs = 0
end if

Set oShell = CreateObject("WScript.Shell")

i=InStr(Wscript.Arguments(1),":")
Thepath=Mid(Wscript.Arguments(1),i+1)

oRegExp.Pattern="/freedav/vid"
' To prevent attack 
if (oRegExp.test(Thepath)) then
	if (OpenAs) then
	  'Cmd = "rundll32.exe c:\WINDOWS\system32\shell32.dll,OpenAs_RunDLL " & Wscript.Arguments(1)
	  Thepath = replace(Thepath,"/","\") 
	  'Cmd = "rundll32.exe c:\WINDOWS\system32\shell32.dll,OpenAs_RunDLL http:" & Thepath
	Cmd = "rundll32.exe c:\WINDOWS\system32\shell32.dll,OpenAs_RunDLL " & Thepath
	
	else
	  Cmd = """" & replace(Thepath,"/","\") & """"
	  Tab=split(Thepath,"/")
	 'wscript.echo ubound(tab)
	
	 ' for i=0 to ubound(Tab)
	 '   if (i>3) then
	 '     Tab(i)= """" & Tab(i) & """"
	 '   end if
	 ' next
	 ' Cmd = join(Tab,"\")
	end if
	'MsgBox("["&Cmd&"]")
	oShell.Run Cmd
else 
  MsgBox("path must containt freedav location")
end if
