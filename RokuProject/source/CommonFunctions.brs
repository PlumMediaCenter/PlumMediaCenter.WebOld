Function GetVideoJsonUrl() As Dynamic
    'registry = createobject("roRegistry")
    'sec = CreateObject("roRegistrySection", "Authentication")
    'print "Section Exists"; sec.Read("UserRegistrationToken")
    'return sec.Read("UserRegistrationToken")
    return "http://192.168.1.109:8080/PlumVideoPlayer/Web/videos.json"
End Function

Function SetVideoJsonUrl(url As String) As Void
    registry = createobject("roRegistry")
    print "received url: "; url
    sec = CreateObject("roRegistrySection", "Authentication")
    sec.Write("UserRegistrationToken ", "hello")
    sec.Flush()
    success = registry.Flush()
    If success = true Then
        print "Succeeded writing to registry"
    Else
        print "Failed writing to registry"
    End If
End Function