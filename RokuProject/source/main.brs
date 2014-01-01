
Function Main()
    'set the main theme of the application
    SetTheme()

    'add a default facade screen to the app so that the app will only close once this screen has been closed 
    screenFacade = CreateObject("roGridScreen")
    screenFacade.show()
     
    'Check the app configuration. If not configured, prompt the user for all necessary information
    CheckConfiguration()
    
    'Check to make sure that the server specified in the configuration actually exists
    print "Verifying that the server exists."
    messageScreen = GetNewMessageScreen("", "Verifying that the server exists at the provided url...")
    serverExists = API_ServerExists() 
    messageScreen.close()
    
    If serverExists = False Then 
        confirmResult = Confirm("Unable to find PlumVideoPlayer server at the following url. Would you like to update the url? '" + BaseUrl() + "'", "Yes", "No")
        If ConfirmResult = True Then
            print "The user DOES want to fix the broken url. Prompting for that now.";
            GetBaseUrlFromUser()
        Else
            print "The user does NOT want to fix the broken url. Continue as if the server was working"
        End If
    End If
    
    'Show the video grid
    ShowVideoGrid()
    'exit the app gently so that the screen doesn't flash to black
    screenFacade.Close()
    sleep(25)
End Function

Function ShowVideoGrid()
    'if the video grid has not yet been created, create it
    If m.videoGrid = invalid Then
        m.videoGrid = CreateObject("roGridScreen")
    End If 
    'grab the videoGrid from the global variables
    grid = m.videoGrid
    
    messageScreen = GetNewMessageScreen("", "Loading videos...")

    'load the library from the server. This will replace the global library object with a new one from the server
    LoadLibrary()
    
    port = CreateObject("roMessagePort")
    grid.SetMessagePort(port) 
    
    rowTitles = []
    rowTitles.Push("Tv Shows")
    rowTitles.Push("Movies")
    rowTitles.Push("Settings")
    grid.SetupLists(rowTitles.Count())
    grid.SetListNames(rowTitles) 
    
    this = GetGlobalAA()
    gridList = []
    tvShows = m.lib.tvShows
    movies = m.lib.movies
    gridList.push(tvShows)
    gridList.push(movies)

    'Add Tv Shows
    tvShowCount = 0
    tvShowList = []
    For Each show in m.lib.tvShows
        o = CreateObject("roAssociativeArray")
        o.ContentType = "series"
        o.Title = show.title
        o.SDPosterUrl = show.sdPosterUrl
        o.HDPosterUrl = show.hdPosterUrl
        o.ShortDescriptionLine1 = "[ShortDescriptionLine1]"
        o.ShortDescriptionLine2 = "[ShortDescriptionLine2]"
        o.Description = show.plot
        o.Rating = show.mpaa
        'o.Length = 1000
        o.NumEpisodes = show.episodeCount
        'o.StarRating = "75"
        o.ReleaseDate = show.year
        o.TextAttrs = { 
            Color:"#FFCCCCCC", 
            Font:"Small", 
            HAlign:"HCenter", 
            VAlign:"VCenter", 
            Direction:"LeftToRight" 
        }
        'o.Length = 5400
        o.Actors = []
        
        For Each actor in show.actorList
            name = actor.name   
            o.Actors.push(name)
        End For
        o.Director = "[Director]"
        tvShowList.Push(o)
        tvShowCount = tvShowCount + 1
    End For
    'add the tv shows tile list to the grid    
    grid.SetContentList(0, tvShowList) 

    'Add movies
    movieList = []
    For Each mov in m.lib.movies
        o = CreateObject("roAssociativeArray")
        o.ContentType = "movie"
        o.Title = mov.title
        o.SDPosterUrl = mov.sdPosterUrl
        o.HDPosterUrl = mov.hdPosterUrl
        o.ShortDescriptionLine1 = "[ShortDescriptionLine1]"
        o.ShortDescriptionLine2 = "[ShortDescriptionLine2]"
        o.Description = mov.plot
        o.Rating = mov.mpaa
        'o.StarRating = "75"
        o.ReleaseDate = mov.year
        'o.Length = 5400
        o.Actors = []
        For Each actor in mov.actorList
            name = actor.name
            o.Actors.push(name)
        End For
        o.Director = "[Director]"
        movieList.Push(o)
    End For
    grid.SetContentList(1, movieList) 
    
    settingsList = []
    'settings menu items
    'set video.json
    s = CreateObject("roAssociativeArray")
    s.ContentType = "movie"
    s.SDPosterUrl = "pkg:/images/settings.sd.png"
    s.HDPosterUrl = "pkg:/images/settings.hd.png"
    s.Title = "Set PlumVideoServer url"
    s.Description = "Set the url for the PlumVideoServer that this app will play from."
    settingsList.push(s)
    'refresh list
    s = CreateObject("roAssociativeArray")
    s.ContentType = "movie"
    s.Title = "Refresh videos"
    s.Description = "Refresh the page with the latest videos from the server"
    settingsList.push(s)
    grid.SetContentList(2, settingsList)
    
    grid.Show() 
    'hide the message screen now that the grid has been shown
    messageScreen.Close()
    while true
        msg = wait(0, port)
        If type(msg) = "roGridScreenEvent" Then
            If msg.isScreenClosed() Then
                Return -1
            Else If msg.isListItemFocused()
                print "Focused msg: ";msg.GetMessage();"row: ";msg.GetIndex();
                print " col: ";msg.GetData()
             Else If msg.isListItemSelected()
                print "Selected msg: ";msg.GetMessage();"row: ";msg.GetIndex();
                print " col: ";msg.GetData()
                row = msg.GetIndex()
                col = msg.GetData()
                'if the settings item was selected
                If row = 2 Then
                    close = ShowSettings(col)
                    If close = True Then
                        Return -1
                    End If
                Else
                    video = gridList[row][col]
                    If row = 0 Then 
                        If video.episodeCount < 1 Then
                            ShowMessage("", "This show has no episodes")
                        Else
                           ShowTvShowEpisodesGrid(col)
                        End if
                    Else
                        PlayVideo(video)
                    End if
                End if
            End if
        End if
    End While
End Function

'
' Checks that all of the roku configuration 
'
Sub CheckConfiguration()
    print "Checking configuration settings"
    bUrl = BaseUrl()
    If bUrl = invalid Then
        print "PlumVideoPlayer api url is not set. Prompting user to enter url."
        ShowMessage("Setup", "This app must be configured before it can be used. Please follow the instructions")
        print "User clicked ok on the initial setup screen"
        GetServerUrlFromUser()
    Else 
        print "Base URL is set.";bUrl
    End If
End Sub

Sub PlayFirstMovie()
    PlayVideo(m.lib.movies[0])
End Sub


Function PlayFirstEpisode()
    show = m.lib.tvShows[0]
     episode = invalid
     For Each season in show.seasons
        For Each ep in season
            Return PlayVideo(ep)
        End For
     End For
     PlayVideo(episode)
End Function

'
' Loads the library from the server into the m.library global variable
'
Sub LoadLibrary()
    'retrieve the library from the server
    lib = API_GetLibrary()
    m.lib = lib
End Sub

Function ShowTvShowEpisodesGrid(showIndex as integer)
    messageScreen =  GetNewMessageScreen("", "Retrieving tv episodes...")
    port = CreateObject("roMessagePort")
    If m.tvShowGrid = invalid Then
        m.tvShowGrid = CreateObject("roGridScreen")
    End If
    grid = m.tvShowGrid
    grid.SetMessagePort(port) 
    'set the grid to wide so the episode pictures look better
    grid.SetGridStyle("flat-landscape")
    'hold the season lists in a 2d array for easier reference when playing them
    
    gridList = []
    show = m.lib.tvShows[showIndex]
    'get the video id of the video that should be focused in the episode grid as the one to watch
    nextEpisodeVideoId = API_GetNextEpisode(show.videoId)
    
    seasonList = []
    rowTitles = []
    'these two should be populated if there is a tv episode that should be played next. otherwise, it defaults to the first episode in the list
    nextEpisodeSeasonIndex = 0
    nextEpisodeIndex = 0
    seasonIndex = 0
    episodeIndex = 0 
    For Each season in show.seasons
        episodeIndex = 0 
        epList = []
        For Each episode in season
            'if this is the episode to watch, save its position for later when we create the grid
            If episode.videoId = nextEpisodeVideoId Then
                nextEpisodeSeasonIndex = seasonIndex
                nextEpisodeIndex = episodeIndex
            End If
            o = CreateObject("roAssociativeArray")
            o.ContentType = "movie"
            o.Title = Str(episode.episodeNumber) + ". " + episode.title
            o.SDPosterUrl = episode.sdPosterUrl
            o.HDPosterUrl = episode.hdPosterUrl
            o.ShortDescriptionLine1 = "[ShortDescriptionLine1]"
            o.ShortDescriptionLine2 = "[ShortDescriptionLine2]"
            o.Description = episode.plot
            o.Rating = episode.mpaa
            'o.StarRating = "75"
            o.ReleaseDate = episode.year
            'o.Length = 5400
            o.Actors = []
            o.url = episode.url
            o.videoId = episode.videoId
            For Each actor in episode.actorList
                name = actor.name
                o.Actors.push(name)
            End For
            o.Director = "[Director]"
            epList.Push(o)
            episodeIndex = episodeIndex + 1
        End For
        seasonList.push(epList)
        seasonIndex = seasonIndex + 1
        'add the season number that the last episode in this list had...they should all be the same season
        rowTitles.push("Season " + Str(episode.seasonNumber) )
    End For
   
    grid.SetupLists(seasonList.Count())
    grid.SetListNames(rowTitles) 
    i = 0
    'spin through the list of seasons and add each list to the grid
    For Each season in seasonList
        gridList.push(season)
        grid.SetContentList(i, season) 
        i = i + 1
    End For
    'focus the grid on the episode that was marked as 'next'. 
    print "Next Episode grid indexes:: ";nextEpisodeSeasonIndex; " - ";nextEpisodeIndex 
    grid.SetFocusedListItem(nextEpisodeSeasonIndex, nextEpisodeIndex)    
    'hide the message
    messageScreen.Close()
    grid.Show() 
    while true
        msg = wait(0, port)
        print msg
        If type(msg) = "roGridScreenEvent" then
            If msg.isScreenClosed() then
                m.tvShowGrid = invalid
                Return -1
            Else If msg.isListItemFocused()
                print "Focused msg: ";msg.GetMessage();"row: ";msg.GetIndex();
                print " col: ";msg.GetData()
             Else If msg.isListItemSelected()
                print "Selected msg: ";msg.GetMessage();"row: ";msg.GetIndex();
                print " col: ";msg.GetData()
                row = msg.GetIndex()
                col = msg.GetData()
                PlayVideo(gridList[row][col])
                'whenever the video has finished playing, reload this grid
                Return ShowTvShowEpisodesGrid(showIndex)
            End If
        endif
    End While
End Function

Sub PlayVideo(pVideo as Object)
    messageScreen = GetNewMessageScreen("", "Preparing video...")
    print pVideo
    startSeconds = API_GetVideoProgress(pVideo.videoId)
    print "Start Seconds";startSeconds
    resume = true
    If startSeconds > 0 Then
        hmsString = GetHourMinuteSecondString(startSeconds)
        'for debugging purposes, skip the confirm window for now
        result = ConfirmWithCancel("Resume where you left off?(" + hmsString + ")", "Resume", "Restart")
        print "Confirm Result: ";result
        If result = 2 Then
            print "PlayVideo: resuming playback at ";startSeconds;" seconds"
            resume = true
        Else If result = 1 Then
            print "PlayVideo: restarting video from beginning"
            resume = false
        Else
            print "PlayVideo: cancel video playback"
            return 
        End If
    End If
    If resume Then
        startMilliseconds = startSeconds * 1000
        'print "PlayVideo: resuming playback at ";startSeconds;" seconds"
    Else
        'print "Restart playback"
        startMilliseconds = -1
    End If
    video  = CreateObject("roAssociativeArray")
    port = CreateObject("roMessagePort")
    screen = CreateObject("roVideoScreen") 
    ' Note: HDBranded controls whether the "HD" logo is displayed for a 
    '       title. This is separate from IsHD because its possible to
    ' have an HD title where you don't want to show the HD logo 
    ' branding for the title. Set these two as appropriate for 
    ' your content
    video.IsHD = true    
    video.HDBranded = false

    ' Note: The preferred way to specify stream info in v2.6 is to use
    ' the Stream roAssociativeArray content meta data parameter. 
    print "Play Video...Url:";pVideo.url
    video.Stream = { 
        url: pVideo.url
        bitrate:0
        StreamFormat:  "mp4"
    }
    
   ' now just tell the screen about the title to be played, set the 
   ' message port for where you will receive events and call show to 
   ' begin playback.  You should see a buffering screen and then 
   ' playback will start immediately when we have enough data buffered. 
    screen.SetContent(video)
    screen.SetMessagePort(port)
    'every 10 seconds, fire a position notification
    screen.SetPositionNotificationPeriod(3)
    screen.Show() 
    'hide the message screen
    messageScreen.Close()
    
    m.lastVideoProgressUpdateTime = CreateObject("roDateTime")
   ' Wait in a loop on the message port for events to be received.  
   ' We will just quit the loop and Return to the calling function 
   ' when the users terminates playback, but there are other things 
   ' you could do here like monitor playback position and see events 
   ' from the streaming player.  Look for status messages from the video 
   ' player for status and failure events that occur during playback 
    while true
       msg = wait(0, port)
        
       if type(msg) = "roVideoScreenEvent" then
           if msg.isStreamStarted() and startMilliseconds > -1
                print "Stream started. Seeking to milliseconds: "; startMilliseconds 
                screen.Seek(startMilliseconds)
                startMilliseconds = -1
          Else if msg.isScreenClosed()
               print "Screen closed"
               exit while
            Else If msg.isStatusMessage()
                  print "status message: "; msg.GetMessage()
            Else If msg.isPlaybackPosition()
                seconds = msg.GetIndex()
                'print "PlayVideo: playback position: ";seconds; " seconds"
                API_SetVideoProgress(pVideo.videoId, seconds)
            Else If msg.isFullResult()
                  print "playback completed"
                  exit while
            Else If msg.isPartialResult()
                  print "playback interrupted"
                  exit while
            Else If msg.isRequestFailed()
                  print "request failed – error: "; msg.GetIndex();" – "; msg.GetMessage()
                  ShowMessage("Error", "There was a problem playing the video. It probably isn't in the proper format. Here is the formal error message: " + msg.GetMessage())
                  exit while
            End If
       End If
    End While 
End Sub
