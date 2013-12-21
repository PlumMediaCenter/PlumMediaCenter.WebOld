'Displays a custom keyboard that does not limit you like the
Function Keyboard(instructions) as Object
    keyX = 90
    keyY = 130
    defaultValue = "http://192.168.1.109:8080/PlumVideoPlayer/Web/"
    resultText = ""
    If defaultValue <> invalid Then
        resultText = defaultValue
    End If
    
    m.white=&hFFFFFFFF
    m.black = &h000000FF
    keyScreen = CreateObject("roScreen")
    keyScreen.SetAlphaEnable(true)
    keyScreen.Clear(m.white)
    
    DisplayResultText(keyScreen, resultText)
       
    'create an array of keys each at their own position on the screen
    keys = DrawAndGetKeys(keyScreen, keyX, keyY, 0, 0)

    keyScreen.finish()
    rowIdx = 0
    colIdx = 0
    
    port = CreateObject("roMessagePort")
    keyScreen.SetMessagePort(port)
    While True
        msg = wait(0, port)
        
        'up down left right btns
        If msg > 101 and msg < 106 Then
            If msg = 102 Then
                rowIdx = rowIdx - 1
            Else If msg = 103 Then
                rowIdx = rowIdx + 1
            Else If msg = 104 Then
                colIdx = colIdx - 1
            Else If msg = 105 Then
                colIdx = colIdx + 1
            End If
            print "rowIdx: ";rowIdx;" colIdx: ";colIdx
            rowIdx = (rowIdx + keys.Count()) mod keys.Count()
            colIdx = (colIdx + keys[rowIdx].Count()) mod keys[rowIdx].Count()
            print "new rowIdx: ";rowIdx;" new colIdx";colIdx
            
            'redraw the keyboard screen, highlighting the selected item
            DrawAndGetKeys(keyScreen, keyX, keyY, rowIdx, colIdx)
        End If
        'OK key
        If msg = 106 Then
        'Grab the value of the selected character 
            resultText = resultText + keys[rowIdx][colIdx].char
            print "result text='";resultText;"'"
            DisplayResultText(keyScreen, resultText)
        End If
        'Rewind button
        If msg = 108 Then
            'trim off the end character
            resultText = resultText.Mid(0, resultText.Len() - 1)
            DisplayResultText(keyScreen, resultText)
        End If
        print msg
     '   If type(msg) = "roGridScreenEvent" Then
     '   End If
    End While
End Function

Function DisplayResultText(keyScreen, resultText as String)
    fontRegistry = CreateObject("roFontRegistry")
    font = fontRegistry.GetDefaultFont(18, false, false)
    x = 70
    y = 50
    'draw the text rectangle at the top of the screen
    keyScreen.DrawRect(x, y, keyScreen.GetWidth()-140, 40, m.black)
    keyScreen.DrawText(resultText, x, y, m.white, font)
End Function

Function DrawAndGetKeys(keyScreen, startx, starty, selectedRowIdx, selectedColIdx)
    buttonSize = 45
    spacing = 5
    charMargin = 4
    normalColor = m.white
    selectedColor = &hFFAAAAAF 
    keys = []
    keys.push(["a", "b", "c", "d", "e", "f", "g", "1", "2", "3"])
    keys.push([ "h", "i", "j", "k", "l", "m", "n",  "4", "5", "6"])
    keys.push(["o", "p", "q", "r", "s", "t", "u", "7", "8", "9"])
    keys.push(["v", "w", "x", "y", "z", "_", "-", "0", ".", "," ])
    keys.push(["<", ">", " ", "?", "/", "\", ":"]) 
    fontRegistry = CreateObject("roFontRegistry")
    font = fontRegistry.GetDefaultFont()
    result = []
    yval = starty
    rowIdx = 0
    For Each row in keys
        colIdx = 0
        'row = keys[rowIdx]
        rowResult = []
        'reset the xval to the starting x position every row
        xval = startx

        colCount = 0
        For Each key in row
            'key = row[idx]

            item =  {x: xval, y: yval, char: key}
            rowResult.push(item)
            'draw this key on the screen
            'draw a box
            keyScreen.DrawRect(item.x, item.y, buttonSize, buttonSize, m.black )
            'draw the text of the key
            If colIdx = selectedColIdx and rowIdx = selectedRowIdx Then
                colr = selectedColor
            Else
                colr = normalColor
            End If
            keyScreen.DrawText(item.char , item.x + charMargin, item.y - charMargin, colr, font)
            colCount = colCount + 1
            'if colCount is a multiple of 3, add some extra spacing between letters
            If colCount = 3 or colCount = 7 Then
                xval = xval + spacing + spacing
            End If
            xval = xval + buttonSize + spacing
            colIdx = colIdx + 1
        End For
        result.push(rowResult)
        rowIdx = rowIdx + 1
        'increment the yval every row
        yval = yval + buttonSize + spacing
    End For
    return result
End Function
