'
' Retrieves the library json file from the server. If that was unsuccessful, 
' this function returns an empty library object
' @return Object  - the library object if successful, an empty library object if unsuccessful 
'
Function API_GetLibrary() as Object
    'temporarily override the baseUrl value so we can get it in the registry without hvaing to go over to the roku
    'SetBaseUrl("http://192.168.1.109:8080/PlumVideoPlayer/Web/")
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

'
' Gets the next episode videoId for the specified tv show
'
Function API_GetNextEpisode(tvShowVideoId as Integer) as Object
    url = BaseUrl() + "api/GetNextEpisode.php?videoId=" + tvShowVideoId.ToStr()
    print "API-GetNextEpisode: ";url
    result = GetJson(url)
    If result = invalid Then
        print "API-GetNextEpisode: invalid"
    Else
        print "API-GetNextEpisode: success"
    End If
    return result
End Function

'
' Wraps the API_GetNextEpisode call and only returns the episode videoId
'
Function API_GetNextEpisodeId(tvShowVideoId as Integer) as Integer
    episode = API_GetNextEpisode(tvShowVideoId)
    episodeId = -1
    If episode = invalid Then
        episodeId = -1
    Else
        episodeId = episode.videoId
    End If
    print "API-GetNextEpisodeId: EpisodeId->";episodeId
    return episodeId
End Function

'
' Returns an object containing the tv show with the videoId requested, as well as all of the episodes in that show
'
Function API_GetTvShow(tvShowVideoId as Integer) as Object
    url = BaseUrl() + "api/GetTvShow.php?videoId=" + tvShowVideoId.ToStr()
    print "API-GetTvShow: ";url
    result = GetJson(url)
    If result <> invalid Then
        print "API-GetTvShow: showId=";tvShowVideoId;", success"
    Else
        print "API-GetTvShow: showId=";tvShowVideoId;", result=invalid"
    End If
    return result
End Function

'
'Get the current second number to start a video at 
'
Function API_GetVideoProgress(videoId as Integer) as Integer
    url = BaseUrl() + "api/GetVideoProgress.php?videoId=" + videoId.ToStr()
    print "API-GetVideoProgress: ";url
    progress = GetJson(url)
    startSeconds = progress.startSeconds
    print "API-GetVideoProgress: videoId=";videoId;". result (startSeconds)=";startSeconds
    return startSeconds 
End Function


'
'Set the current second number the video is playing at
'
Sub API_SetVideoProgress(videoId as Integer, seconds as Integer)
    strSeconds = seconds.ToStr()
    strVideoId = videoId.ToStr()
    url = BaseUrl() + "api/SetVideoProgress.php?videoId=" + strVideoId + "&seconds=" + strSeconds
    result = GetJSON(url)
    success = result.success
    print "API-SetVideoProgress: videoId=";strVideoId;", seconds=";strSeconds;", success=";success
End Sub

'
' Determines if the server is currently visible or not. 
'
Function API_ServerExists() as Boolean
    mBaseUrl = BaseUrl()
    If mBaseUrl = invalid Then
        Return false
    End If
    
    url = mBaseUrl + "api/ServerExists.php"
    result = GetJSONBoolean(url)
    success = result
    print "API-ServerExists: url=";url;" Success=";success
    return success
End Function
