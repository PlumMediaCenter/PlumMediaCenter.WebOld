Sub ShowSettings(n)
    If (n = 0) Then
        jsonUrl = ShowGetBaseUrlFromUser()
        SetRegVal("baseUrl", jsonUrl)
    Else If (n = 1) Then
        print "Refresh media list"
        Main()
        
    End If
End Sub

Function GetBaseUrlFromUser() as Dynamic
    print "Setting up Base URL Promt Screen"
    screen = CreateObject("roKeyboardScreen")
    screen.SetText("http://192.168.1.109:8080/PlumVideoPlayer/Web/")
    port = CreateObject("roMessagePort") 
    screen.SetMessagePort(port)
    screen.SetTitle("PlumVideoPlayer Web URL")
    screen.SetDisplayText("Enter the url for the PlumVideoPlayer API.")
    screen.SetMaxLength(8)
    screen.AddButton(1, "Ok")
    screen.AddButton(2, "Cancel")
    screen.Show() 
    print "Prompting user for Base Url"
     while true
         msg = wait(0, screen.GetMessagePort()) 
         'print "message received"
         If type(msg) = "roKeyboardScreenEvent"
             If msg.isScreenClosed()
                 Return invalid
             Else If msg.isButtonPressed() then
                 If msg.GetIndex() = 1
                    sBaseUrl = screen.GetText()
                    'save the base url to the registry
                    SetBaseUrl(sBaseUrl)
                    print "User said that the base url was ";sBaseUrl
                    'see if the server exists at the url the user specified
                    serverExists = API_ServerExists()
                    'if the server exists, use this url
                    If serverExists = true Then
                        print "Server exists. Setting base url=";sBaseUrl
                        SetBaseUrl(sBaseUrl)
                        Return true
                    Else
                        stillSave = Confirm("Server does not exist. Do you still want to use this url?","Yes","No")
                        If stillSave = true Then
                            print "Server does not exist. Setting base url anyway. url=";sBaseUrl
                            SetBaseUrl(sBaseUrl)
                            Return true
                        Else
                            return GetBaseUrlFromUser()
                        End If
                    End If
                 End If
             End If
         End If
     End While 
     Return invalid
 End Function

