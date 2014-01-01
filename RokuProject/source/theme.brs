'
' Sets the theme of the application
'
Sub SetTheme()
    'these are the standard values used throughout the theme. Some of the different pages need to have the values re-set to override
    'the defaults, so rather than changing the values twise, set the once here and reference them by value
    overhangSliceHD =  "pkg:/images/overhang_hd.png"
    overhangSliceSD =  "pkg:/images/overhang_sd.png"
    greyBackgroundColor = "#363636"
    GridScreenOverhangHeightHD = "100"
    GridScreenOverhangHeightSD = "62"
    lightBlue = "#dbf2fa"
    solidBlue = "#7ddcf0"
    lavender = "#95669c"
    deepPurple = "#553588"
    tan = "#f0bd7e"
    pink = "#fff3d9"
    
    'apply the theme
    app = CreateObject("roAppManager")
    theme = CreateObject("roAssociativeArray")
    theme.BackgroundColor = greyBackgroundColor
    'can only use a greyscale for the gridscreen background color
     theme.GridScreenBackgroundColor = greyBackgroundColor
    theme.OverhangSliceHD = overhangSliceHD
    theme.OverhangSliceSD = overhangSliceSD
    theme.GridScreenOverhangSliceHD = overhangSliceHD
    theme.GridScreenOverhangSliceSD = overhangSliceSD
    theme.GridScreenOverhangHeightHD = GridScreenOverhangHeightHD
    theme.GridScreenOverhangHeightSD = GridScreenOverhangHeightSD 
    app.SetTheme(theme)    
End Sub