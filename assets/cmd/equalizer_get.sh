amixer -D equal sget "01. 31 Hz"  | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "02. 63 Hz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "03. 125 Hz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "04. 250 Hz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "05. 500 Hz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "06. 1 kHz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "07. 2 kHz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "08. 4 kHz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "09. 8 kHz" | grep 'Front Left: Playback' | awk {'print $4'}
amixer -D equal sget "10. 16 kHz" | grep 'Front Left: Playback' | awk {'print $4'}




