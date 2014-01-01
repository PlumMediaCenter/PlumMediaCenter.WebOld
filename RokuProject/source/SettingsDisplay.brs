Function ShowSettings(n)
    If (n = 0) Then
        success = GetBaseUrlFromUser()
            If success <> invalid Then
            'refresh the video grid
            print "reload the video grid now that the user has redefined the server url."
            ShowVideoGrid()
            return True
        End if
    Else If (n = 1) Then
        print "Refresh media list"
        ShowVideoGrid()
        Return True
    End If
End Function

Function GetBaseUrlFromUser() as Dynamic
    print "Setting up Base URL Promt Screen"
    screen = CreateObject("roKeyboardScreen")
    'get the serverUrl from the registry
    serverUrl = BaseUrl()
    'if the server url is not set in the registry, use a default value
    If serverUrl = invalid Then
        serverUrl = "http://ip_address_or_server_name/PlumVideoPlayer/Web/"
    End If
    'set the default value of the keyboard screen
    screen.SetText(serverUrl)
    port = CreateObject("roMessagePort") 
    screen.SetMessagePort(port)
    screen.SetTitle("PlumVideoPlayer Web URL")
    screen.SetDisplayText("Enter the url for the PlumVideoPlayer API.")
    screen.SetMaxLength(800)
    screen.AddButton(1, "Ok")
    screen.AddButton(2, "Cancel")
    screen.Show() 
    print "Prompting user for Base Url"
     while true
         msg = wait(0, screen.GetMessagePort()) 
         print "message received";msg
         If type(msg) = "roKeyboardScreenEvent"
             If msg.isScreenClosed()
                 Return invalid
             Else If msg.isButtonPressed() then
                 If msg.GetIndex() = 1
                    sBaseUrl = screen.GetText()
                    'save the base url to the registry
                    SetBaseUrl(sBaseUrl)
                    print "User said that the PlumVideoPlayer url was ";sBaseUrl
                    messageScreen = GetNewMessageScreen("", "Verifying that the server exists at the provided url...")
                    'see if the server exists at the url the user specified
                    serverExists = API_ServerExists()
                    messageScreen.close()
                    'if the server exists, use this url
                    If serverExists = true Then
                        print "Server exists. Setting the PlumVideoPlayer url to=";sBaseUrl
                        SetBaseUrl(sBaseUrl)
                        ShowMessage("Success", "Successfully connected to the PlumVideoPlayer server at the specified url.")
                        Return true
                    Else
                        stillSave = Confirm("PlumVideoPlayer Server does not exist at the provided url. Do you still want to use this url?","Yes","No")
                        If stillSave = true Then
                            print "PlumVideoPlayer Server does not exist at the provided url. Setting url anyway. url=";sBaseUrl
                            SetBaseUrl(sBaseUrl)
                            Return true
                        Else
                            return GetBaseUrlFromUser()
                        End If
                    End If
                Else
                    print "User cancled entering the PlumVideoPlayer url"
                    Return false 
                 End If
             End If
         End If
     End While 
     Return invalid
End Function

