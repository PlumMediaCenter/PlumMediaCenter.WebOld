Sub ShowSettings(n)
    If (n = 0) Then
        jsonUrl = ShowGetBaseUrlFromUser()
        SetRegVal("baseUrl", jsonUrl)
    Else If (n = 1) Then
        print "Refresh media list"
        Main()
        
    End If
End Sub

Function ShowGetBaseUrlFromUser() as Dynamic
    screen = CreateObject("roKeyboardScreen")
     screen.SetText("http://192.168.1.109:8080/PlumVideoPlayer/Web/")
    port = CreateObject("roMessagePort") 
     screen.SetMessagePort(port)
     screen.SetTitle("Enter Base PlumVideoPlayer URL")
     screen.SetDisplayText("Enter the PlumVideoPlayer base website url (the one that you are running on a local server).")
     screen.SetMaxLength(8)
     screen.AddButton(1, "Ok")
     screen.AddButton(2, "Cancel")
     screen.Show() 
  
     while true
         msg = wait(0, screen.GetMessagePort()) 
         print "message received"
         If type(msg) = "roKeyboardScreenEvent"
             If msg.isScreenClosed()
                 Return invalid
             Else If msg.isButtonPressed() then
                 If msg.GetIndex() = 1
                     Return screen.GetText()
                 End If
             End If
         End If
     End While 
     Return invalid
 End Function

