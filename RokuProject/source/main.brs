
Function Main()

    'load the library from the remote json file
    LoadLibrary()
    
    port = CreateObject("roMessagePort")
    grid = CreateObject("roGridScreen")
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
        'o.StarRating = "75"
        o.ReleaseDate = show.year
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
    s.Title = "Set video.json Url"
    s.Description = "Set the video.json url for this application"
    settingsList.push(s)
    'refresh list
    s = CreateObject("roAssociativeArray")
    s.ContentType = "movie"
    s.Title = "Refresh videos"
    s.Description = "Refresh the page with the latest videos from the server"
    settingsList.push(s)
    grid.SetContentList(2, settingsList)

    grid.Show() 
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
                    ShowSettings(col)
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
' Loads the library from the server into the m.library global variable
'
Sub LoadLibrary()
    'retrieve the library from the server
    lib = API_GetLibrary()
    m.lib = lib
End Sub

Function ShowTvShowEpisodesGrid(showIndex as integer)
    'print "Printing season from show episodes: ";m.lib.tvShows[selectedIndex].seasons["1"]
    'print "Selected Index:";selectedIndex
    port = CreateObject("roMessagePort")
    grid = CreateObject("roGridScreen")
    grid.SetMessagePort(port) 
    'set the grid to wide so the episode pictures look better
    grid.SetGridStyle("flat-landscape")
    'hold the season lists in a 2d array for easier reference when playing them
    
    gridList = []
    show = m.lib.tvShows[showIndex]
    
    seasonList = []
    rowTitles = []
    For Each season in show.seasons
        epList = []
        For Each episode in season
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
            For Each actor in episode.actorList
                name = actor.name
                o.Actors.push(name)
            End For
            o.Director = "[Director]"
            epList.Push(o)
        End For
        seasonList.push(epList)
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

    grid.Show() 
    while true
        msg = wait(0, port)
        if type(msg) = "roGridScreenEvent" then
            if msg.isScreenClosed() then
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
                'PlayVideo(gridList[0][0])
            endif
        endif
    End While
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

Function PlayVideo(video)

    episode = CreateObject("roAssociativeArray")
    
    port = CreateObject("roMessagePort")
    screen = CreateObject("roVideoScreen") 
    ' Note: HDBranded controls whether the "HD" logo is displayed for a 
    '       title. This is separate from IsHD because its possible to
    ' have an HD title where you don't want to show the HD logo 
    ' branding for the title. Set these two as appropriate for 
    ' your content
    episode.IsHD = true    
    episode.HDBranded = false

    ' Note: The preferred way to specify stream info in v2.6 is to use
    ' the Stream roAssociativeArray content meta data parameter. 
    print "Play Video...Url:"
    print video.url
     episode.Stream = { 
        url: video.url
        bitrate:2000
        StreamFormat:  "mp4"
    }

   ' now just tell the screen about the title to be played, set the 
   ' message port for where you will receive events and call show to 
   ' begin playback.  You should see a buffering screen and then 
   ' playback will start immediately when we have enough data buffered. 
    screen.SetContent(episode)
    screen.SetMessagePort(port)
    screen.Show() 
   ' Wait in a loop on the message port for events to be received.  
   ' We will just quit the loop and Return to the calling function 
   ' when the users terminates playback, but there are other things 
   ' you could do here like monitor playback position and see events 
   ' from the streaming player.  Look for status messages from the video 
   ' player for status and failure events that occur during playback 
    while true
       msg = wait(0, port)
    
       if type(msg) = "roVideoScreenEvent" then
           print "showVideoScreen | msg = "; msg.GetMessage() " | index = "; msg.GetIndex()
           if msg.isScreenClosed()
               print "Screen closed"
               exit while
            Else If msg.isStatusMessage()
                  print "status message: "; msg.GetMessage()
            Else If msg.isPlaybackPosition()
                  print "playback position: "; msg.GetIndex()
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
End Function