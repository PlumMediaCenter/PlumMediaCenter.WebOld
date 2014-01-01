'
' Retrieves the registry value in the provided section and at the specified key
' The registry technically lets you organize your registry values by category and name.
' Most of the time it doesn't seem that many registry items are needed, so this function
' just saves everyting in the 'Settings' category. 
'
Function GetRegVal(name as String) As Dynamic
    section = "Settings"
    sec = CreateObject("roRegistrySection", section)
     if sec.Exists(name)  
         return sec.Read(name)
     endif
     return invalid
End Function

'
' Saves a value to the registry in the 'Settings category
' @param string name - the name of the variable to be saved in the registry
' @param string value - the value to save into the registry
'
Function SetRegVal(name as String, value as String) As Void
    section = "Settings" 
    sec = CreateObject("roRegistrySection", section)
    sec.Write(name, value)
    sec.Flush()
End Function


'
' Deletes a registry setting from the registry
'
Function DeleteRegVal(name as String) as Void
    section = "Settings" 
    sec = CreateObject("roRegistrySection", section)
    sec.Delete(name)
    sec.Flush()
End Function

'
' Performs a network request, returning the json result as an object
' @param string sUrl - the url to request
' @return object - the object created from the result json.
'
Function GetJSON(sUrl as String) as Object
    searchRequest = CreateObject("roUrlTransfer") 
    searchRequest.SetURL(sUrl)
    result = searchRequest.GetToString() 
    obj = ParseJson(result)
    return obj    
End Function

'
' For some reason, brightscript doesn't like to convert json into a boolean value. 
' Perform a web request and expect a boolean value back, either true or false.
'
Function GetJSONBoolean(sUrl as String) as Boolean
  searchRequest = CreateObject("roUrlTransfer") 
    searchRequest.SetURL(sUrl)
    result = searchRequest.GetToString() 
    If result = "true" Then
        return true
    Else
        return false
    End If
End Function

'
' Sends a nonblocking request in which the return result is not important. 
' This is useful for update requests and such.
'
Sub FireNonBlockingRequest(sUrl as String)
    'print "FireNonBlockingRequest: ";sUrl
    searchRequest = CreateObject("roUrlTransfer") 
    searchRequest.SetURL(sUrl)
    'send the request 
    searchRequest.AsyncGetToString() 
End Sub

Function GetNewMessageScreen(messageTitle as String, message as String) as Object
    dialog = CreateObject("roMessageDialog")
    dialog.SetTitle(messageTitle)
    dialog.SetText(message)
    dialog.Show()
    dialog.ShowBusyAnimation() 
    return dialog
End Function

Sub ShowMessage(messageTitle as String, message as String)
    port = CreateObject("roMessagePort")
    dialog = CreateObject("roMessageDialog")
    dialog.SetMessagePort(port) 
    dialog.SetTitle(messageTitle)
    dialog.SetText(message)
 
    dialog.AddButton(1, "Ok")
    dialog.EnableBackButton(true)
    dialog.Show()
    While True
        dlgMsg = wait(0, dialog.GetMessagePort())
        If type(dlgMsg) = "roMessageDialogEvent"
            if dlgMsg.isButtonPressed()
                if dlgMsg.GetIndex() = 1
                    exit while
                End If
            Else If dlgMsg.isScreenClosed()
                exit while
            End If
        End If
    End While
End Sub

'
' Prompts the user for a yes/no answer, returns the result
' @return boolean - true if user selects yes, false if user selects no.
Function Confirm(message, yesText as String, noText as String) as Boolean
    print "Confirming: '" + message + "', '" + yesText + "', " + noText + "'"
    port = CreateObject("roMessagePort")
    dialog = CreateObject("roMessageDialog")
    dialog.SetMessagePort(port) 
    'dialog.SetTitle(messageTitle)
    dialog.SetText(message)
 
    dialog.AddButton(1, yesText)
    dialog.AddButton(0, noText)
    dialog.EnableBackButton(true)
    dialog.Show()
    While True
        dlgMsg = wait(0, dialog.GetMessagePort())
        If type(dlgMsg) = "roMessageDialogEvent"
            if dlgMsg.isButtonPressed()
                If dlgMsg.GetIndex() = 0
                    print "User chose ";noText 
                    Return False
                End If
                If dlgMsg.GetIndex() = 1
                  print "User chose ";yesText 
                    Return True
                End If
            Else If dlgMsg.isScreenClosed()
                exit while
            End If
        End If
    End While
    'default to return false
    print "User chose cancel or back, which means ";noText 
    Return false
End Function

'
' Prompts the user for a yes/no/cancel answer, returns the result
' @return boolean - true if user selects yes, false if user selects no. -1 if the user selects cancel
Function ConfirmWithCancel(message, yesText as String, noText as String) as Integer
    print "Confirming: '" + message + "', '" + yesText + "', " + noText + "', 'Cancel'"
    port = CreateObject("roMessagePort")
    dialog = CreateObject("roMessageDialog")
    dialog.SetMessagePort(port) 
    'dialog.SetTitle(messageTitle)
    dialog.SetText(message)

    dialog.AddButton(0, yesText)
    dialog.AddButton(1, noText)
    dialog.AddButton(2, "Cancel")
    dialog.EnableBackButton(true)
    dialog.Show()
    While True
        dlgMsg = wait(0, dialog.GetMessagePort())
        If type(dlgMsg) = "roMessageDialogEvent"
            if dlgMsg.isButtonPressed()
                print dlgMsg.getMessage()
                If dlgMsg.GetIndex() = 0
                    Return 2
                End If
                If dlgMsg.GetIndex() = 1
                    Return 1
                End If
                If dlgMsg.GetIndex() = 2
                    Return 0
                End If
            Else If dlgMsg.isScreenClosed()
                exit while
            End If
        End If
    End While
    'default to return cancel
    Return -1
End Function

'
' Generates a string containing the hours minutes all together for presentation purposes
' @return string - a string with the hours, minutes and seconds in presentation format
'
Function GetHourMinuteSecondString(pSeconds) As String
    'convert the parameter into an integer
    pSeconds = Int(pSeconds)
    'get the number of hours, minutes and seconds
    hours = Int(pSeconds / 3600)
    minutes = Int((pSeconds / 60) mod 60)
    seconds = pSeconds mod 60
    
    resultString = ""
    'Add the hours, if there are any
    If hours > 0 Then
        resultString = hours.ToStr() + " hours "
    End If
    'Add the minutes, if there are any
    If minutes > 0 Then
        resultString = resultString + minutes.ToStr() + " minutes "
    End If
    'add the seconds, if there are any
    If seconds > 0 Then
        resultString = resultString + seconds.ToStr() + " seconds"        
    End If
    return resultString.Trim()
End Function
