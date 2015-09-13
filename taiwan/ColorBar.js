function ColorBar(value) {
    if (value <= 0.05)
        return "white"
    else if (value <= 0.1)
        return "green"
    else if (value <= 0.15)
        return "yellow"
    else if (value <= 0.2)
        return "orange"
    else if (value <= 0.25)
        return "red"
    else if(value > 0.25)
        return "purple"
    else
        return "white"
}
