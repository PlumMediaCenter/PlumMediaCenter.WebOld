'
' Retrieves the registry value in the provided section and at the specified key
'
Function GetRegVal(name) As Dynamic
    section = "Settings"
    sec = CreateObject("roRegistrySection", section)
     if sec.Exists(name)  
         return sec.Read(name)
     endif
     return invalid
End Function

'
' Saves a value to the registry
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
' Performs a network request, returning the json result as an object
' @param string sUrl - the url to request
' @return object - the object created from the result json.
'
Function GetJSON(sUrl as String) as Object
    searchRequest = CreateObject("roUrlTransfer") 
    searchRequest.SetURL(sUrl)
    result = ParseJson(searchRequest.GetToString())
    return result
End Function