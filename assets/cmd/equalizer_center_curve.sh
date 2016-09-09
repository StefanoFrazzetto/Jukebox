if [ $# != 10 ]; then
exit 1
fi
down="0"
up="0"
low="0"
high="100"
bar1=$1
bar2=$2
bar3=$3
bar4=$4
bar5=$5
bar6=$6
bar7=$7
bar8=$8
bar9=$9
bar10=${10}


if [ $bar1 -le $bar2 ] && [ $bar1 -le $bar3 ] && [ $bar1 -le $bar4 ] && [ $bar1 -le $bar5 ] && [ $bar1 -le $bar6 ] && [ $bar1 -le $bar7 ] && [ $bar1 -le $bar8 ] && [ $bar1 -le $bar9 ] && [ $bar1 -le $bar10 ]; then
low=$bar1
fi
if [ $bar2 -le $bar1 ] && [ $bar2 -le $bar3 ] && [ $bar2 -le $bar4 ] && [ $bar2 -le $bar5 ] && [ $bar2 -le $bar6 ] && [ $bar2 -le $bar7 ] && [ $bar2 -le $bar8 ] && [ $bar2 -le $bar9 ] && [ $bar2 -le $bar10 ]; then
low=$bar2
fi
if [ $bar3 -le $bar1 ] && [ $bar3 -le $bar2 ] && [ $bar3 -le $bar4 ] && [ $bar3 -le $bar5 ] && [ $bar3 -le $bar6 ] && [ $bar3 -le $bar7 ] && [ $bar3 -le $bar8 ] && [ $bar3 -le $bar9 ] && [ $bar3 -le $bar10 ]; then
low=$bar3
fi
if [ $bar4 -le $bar1 ] && [ $bar4 -le $bar2 ] && [ $bar4 -le $bar3 ] && [ $bar4 -le $bar5 ] && [ $bar4 -le $bar6 ] && [ $bar4 -le $bar7 ] && [ $bar4 -le $bar8 ] && [ $bar4 -le $bar9 ] && [ $bar4 -le $bar10 ]; then
low=$bar4
fi
if [ $bar5 -le $bar1 ] && [ $bar5 -le $bar2 ] && [ $bar5 -le $bar3 ] && [ $bar5 -le $bar4 ] && [ $bar5 -le $bar6 ] && [ $bar5 -le $bar7 ] && [ $bar5 -le $bar8 ] && [ $bar5 -le $bar9 ] && [ $bar5 -le $bar10 ]; then
low=$bar5
fi
if [ $bar6 -le $bar1 ] && [ $bar6 -le $bar2 ] && [ $bar6 -le $bar3 ] && [ $bar6 -le $bar4 ] && [ $bar6 -le $bar5 ] && [ $bar6 -le $bar7 ] && [ $bar6 -le $bar8 ] && [ $bar6 -le $bar9 ] && [ $bar6 -le $bar10 ]; then
low=$bar6
fi
if [ $bar7 -le $bar1 ] && [ $bar7 -le $bar2 ] && [ $bar7 -le $bar3 ] && [ $bar7 -le $bar4 ] && [ $bar7 -le $bar5 ] && [ $bar7 -le $bar6 ] && [ $bar7 -le $bar8 ] && [ $bar7 -le $bar9 ] && [ $bar7 -le $bar10 ]; then
low=$bar7
fi
if [ $bar8 -le $bar1 ] && [ $bar8 -le $bar2 ] && [ $bar8 -le $bar3 ] && [ $bar8 -le $bar4 ] && [ $bar8 -le $bar5 ] && [ $bar8 -le $bar6 ] && [ $bar8 -le $bar7 ] && [ $bar8 -le $bar9 ] && [ $bar8 -le $bar10 ]; then
low=$bar8
fi
if [ $bar9 -le $bar1 ] && [ $bar9 -le $bar2 ] && [ $bar9 -le $bar3 ] && [ $bar9 -le $bar4 ] && [ $bar9 -le $bar5 ] && [ $bar9 -le $bar6 ] && [ $bar9 -le $bar7 ] && [ $bar9 -le $bar8 ] && [ $bar9 -le $bar10 ]; then
low=$bar9
fi
if [ $bar10 -le $bar1 ] && [ $bar10 -le $bar2 ] && [ $bar10 -le $bar3 ] && [ $bar10 -le $bar4 ] && [ $bar10 -le $bar5 ] && [ $bar10 -le $bar6 ] && [ $bar10 -le $bar7 ] && [ $bar10 -le $bar8 ] && [ $bar10 -le $bar9 ]; then
low=$bar10
fi



if [ $bar1 -ge $bar2 ] && [ $bar1 -ge $bar3 ] && [ $bar1 -ge $bar4 ] && [ $bar1 -ge $bar5 ] && [ $bar1 -ge $bar6 ] && [ $bar1 -ge $bar7 ] && [ $bar1 -ge $bar8 ] && [ $bar1 -ge $bar9 ] && [ $bar1 -ge $bar10 ]; then
high=$bar1
fi
if [ $bar2 -ge $bar1 ] && [ $bar2 -ge $bar3 ] && [ $bar2 -ge $bar4 ] && [ $bar2 -ge $bar5 ] && [ $bar2 -ge $bar6 ] && [ $bar2 -ge $bar7 ] && [ $bar2 -ge $bar8 ] && [ $bar2 -ge $bar9 ] && [ $bar2 -ge $bar10 ]; then
high=$bar2
fi
if [ $bar3 -ge $bar1 ] && [ $bar3 -ge $bar2 ] && [ $bar3 -ge $bar4 ] && [ $bar3 -ge $bar5 ] && [ $bar3 -ge $bar6 ] && [ $bar3 -ge $bar7 ] && [ $bar3 -ge $bar8 ] && [ $bar3 -ge $bar9 ] && [ $bar3 -ge $bar10 ]; then
high=$bar3
fi
if [ $bar4 -ge $bar1 ] && [ $bar4 -ge $bar2 ] && [ $bar4 -ge $bar3 ] && [ $bar4 -ge $bar5 ] && [ $bar4 -ge $bar6 ] && [ $bar4 -ge $bar7 ] && [ $bar4 -ge $bar8 ] && [ $bar4 -ge $bar9 ] && [ $bar4 -ge $bar10 ]; then
high=$bar4
fi
if [ $bar5 -ge $bar1 ] && [ $bar5 -ge $bar2 ] && [ $bar5 -ge $bar3 ] && [ $bar5 -ge $bar4 ] && [ $bar5 -ge $bar6 ] && [ $bar5 -ge $bar7 ] && [ $bar5 -ge $bar8 ] && [ $bar5 -ge $bar9 ] && [ $bar5 -ge $bar10 ]; then
high=$bar5
fi
if [ $bar6 -ge $bar1 ] && [ $bar6 -ge $bar2 ] && [ $bar6 -ge $bar3 ] && [ $bar6 -ge $bar4 ] && [ $bar6 -ge $bar5 ] && [ $bar6 -ge $bar7 ] && [ $bar6 -ge $bar8 ] && [ $bar6 -ge $bar9 ] && [ $bar6 -ge $bar10 ]; then
high=$bar6
fi
if [ $bar7 -ge $bar1 ] && [ $bar7 -ge $bar2 ] && [ $bar7 -ge $bar3 ] && [ $bar7 -ge $bar4 ] && [ $bar7 -ge $bar5 ] && [ $bar7 -ge $bar6 ] && [ $bar7 -ge $bar8 ] && [ $bar7 -ge $bar9 ] && [ $bar7 -ge $bar10 ]; then
high=$bar7
fi
if [ $bar8 -ge $bar1 ] && [ $bar8 -ge $bar2 ] && [ $bar8 -ge $bar3 ] && [ $bar8 -ge $bar4 ] && [ $bar8 -ge $bar5 ] && [ $bar8 -ge $bar6 ] && [ $bar8 -ge $bar7 ] && [ $bar8 -ge $bar9 ] && [ $bar8 -ge $bar10 ]; then
high=$bar8
fi
if [ $bar9 -ge $bar1 ] && [ $bar9 -ge $bar2 ] && [ $bar9 -ge $bar3 ] && [ $bar9 -ge $bar4 ] && [ $bar9 -ge $bar5 ] && [ $bar9 -ge $bar6 ] && [ $bar9 -ge $bar7 ] && [ $bar9 -ge $bar8 ] && [ $bar9 -ge $bar10 ]; then
high=$bar9
fi
if [ $bar10 -ge $bar1 ] && [ $bar10 -ge $bar2 ] && [ $bar10 -ge $bar3 ] && [ $bar10 -ge $bar4 ] && [ $bar10 -ge $bar5 ] && [ $bar10 -ge $bar6 ] && [ $bar10 -ge $bar7 ] && [ $bar10 -ge $bar8 ] && [ $bar10 -ge $bar9 ]; then
high=$bar10
fi


high=$(expr 100 - $high)

if [ $high -ge $low ]; then
up=$(expr $high - $low)
up=$(expr $up / 2)
fi

if [ $low -ge $high ]; then
down=$(expr $low - $high)
down=$(expr $down / 2)
fi



if [ $up -gt $down ]; then
bar1=$(expr $bar1 + $up)
bar2=$(expr $bar2 + $up)
bar3=$(expr $bar3 + $up)
bar4=$(expr $bar4 + $up)
bar5=$(expr $bar5 + $up)
bar6=$(expr $bar6 + $up)
bar7=$(expr $bar7 + $up)
bar8=$(expr $bar8 + $up)
bar9=$(expr $bar9 + $up)
bar10=$(expr $bar10 + $up)
fi

if [ $down -gt $up ]; then
bar1=$(expr $bar1 - $down)
bar2=$(expr $bar2 - $down)
bar3=$(expr $bar3 - $down)
bar4=$(expr $bar4 - $down)
bar5=$(expr $bar5 - $down)
bar6=$(expr $bar6 - $down)
bar7=$(expr $bar7 - $down)
bar8=$(expr $bar8 - $down)
bar9=$(expr $bar9 - $down)
bar10=$(expr $bar10 - $down)
fi

echo "fixed curve"
echo $bar1 $bar2 $bar3 $bar4 $bar5 $bar6 $bar7 $bar8 $bar9 $bar10


