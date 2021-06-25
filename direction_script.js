        var d = {
            N: "North",
            NNE: "North-northeast",
            NE: "Northeast",
            ENE: "East-northeast",
            E: "East",
            ESE: "East-southeast",
            SE: "Southeast",
            SSE: "South-southeast",
            S: "South",
            SSW: "South-southwest",
            SW: "Southwest",
            WSW: "West-southwest",
            W: "West",
            WNW: "West-northwest",
            NW: "Northwest",
            NNW: "North-northwest"
        };
        function p(t) {
            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
            if (null === t)
                return null;
            var i = null;
            if (t < 22.5 || t >= 337.5)
                i = "N";
            else if (t < 67.5)
                i = "NE";
            else if (t < 112.5)
                i = "E";
            else if (t < 157.5)
                i = "SE";
            else if (t < 202.5)
                i = "S";
            else if (t < 247.5)
                i = "SW";
            else if (t < 292.5)
                i = "W";
            else {
                if (!(t < 337.5))
                    return null;
                i = "NW"
            }
            return e ? i : d[i]
        }
        function u(t) {
            var e = !(arguments.length > 1 && void 0 !== arguments[1]) || arguments[1];
            if (null === t)
                return null;
            var i = null;
            if (t < 11.25 || t >= 348.75)
                i = "N";
            else if (t < 33.75)
                i = "NNE";
            else if (t < 56.25)
                i = "NE";
            else if (t < 78.75)
                i = "ENE";
            else if (t < 101.25)
                i = "E";
            else if (t < 123.75)
                i = "ESE";
            else if (t < 146.25)
                i = "SE";
            else if (t < 168.75)
                i = "SSE";
            else if (t < 191.25)
                i = "S";
            else if (t < 213.75)
                i = "SSW";
            else if (t < 236.25)
                i = "SW";
            else if (t < 258.75)
                i = "WSW";
            else if (t < 281.25)
                i = "W";
            else if (t < 303.75)
                i = "WNW";
            else if (t < 326.25)
                i = "NW";
            else {
                if (!(t < 348.75))
                    return null;
                i = "NNW"
            }
            return e ? i : d[i]
        }
    },