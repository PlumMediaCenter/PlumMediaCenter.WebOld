'
' Retrieves the library json file from the server. If that was unsuccessful, 
' this function returns an empty library object
' @return Object  - the library object if successful, an empty library object if unsuccessful 
'
Function API_GetLibrary() as Object
    'temporarily override the baseUrl value so we can get it in the registry without hvaing to go over to the roku
    SetBaseUrl("http://192.168.1.109:8080/PlumVideoPlayer/Web/")
    libraryUrl = BaseUrl() + "api/GetLibrary.php"
    'perform a blocking request to retrieve the library object

    lib = GetJSON(libraryUrl)
    
    'if the library was not able to be retrieved, make an empty library object
    If (lib = invalid) Then
        print "Failed to successfully fetch library from server. Using empty library object"
        lib = {
            movies: [], 
            tvShows: []
        }
    Else
        print "Retrieved library from server."
    End if
    
    return lib
End Function

Function API_GetNextEpisode(tvShowVideoId as String) as String
    url = BaseUrl() + "api/GetNextEpisode.php?videoId=" + tvShowVideoId
    result = GetJson(url)
    episodeId = result.videoId
    print "API-GetNextEpisode: showId=";tvShowVideoId;", result=";episodeId
    return episodeId
End Function

'
'Get the current second number to start a video at 
'
Function API_GetVideoProgress(videoId as String) as Integer
    url = BaseUrl() + "api/GetVideoProgress.php?videoId=" + videoId
    progress = GetJson(url)
    startSeconds = progress.startSeconds
    print "API-GetVideoProgress: videoId=";videoId;". result (startSeconds)=";startSeconds
    return startSeconds 
End Function



'
'Set the current second number the video is playing at
'
Sub API_SetVideoProgress(videoId as String, seconds as Integer)
    strSeconds = seconds.ToStr()
    url = BaseUrl() + "api/SetVideoProgress.php?videoId=" + videoId + "&seconds=" + strSeconds
    result = GetJSON(url)
    success = result.success
    print "API-SetVideoProgress: videoId=";videoId;", seconds=";strSeconds;", success=";success
End Sub
