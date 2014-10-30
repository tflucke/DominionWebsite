function replaceAll(find, replace, str)
	{return str.replace(new RegExp(find, 'g'), replace);}

function Card(newName, newSetId, newSetName, newCost, newNote)
	{
		this.name = newName;
		this.setId=newSetId;
		this.setName=newSetName.replace(" ", "").toLowerCase();
		this.cost=newCost;
		this.note=newNote;
	}
Card.prototype.getImage = function()
	{
		return "images/" + this.setName + "/" + replaceAll(' ', '', this.name).replace('\'', "") + ".jpg";
	};
Card.prototype.drawTableCell = function(drawName, drawImage)
	{
		var element = "<td class='cardCell " + this.setName + "'>";
		if (drawName)
			{element = element + this.name;}
		if (this.note != null)
			{element = element + " (" + this.note + ")";}
		if (drawImage)
			{element = element + "<img class='cardImg' src='" + this.getImage() + "' />";}
		element = element + "</td>";
		return element;
	};

function makeSortFn(keys, fns, i)
	{
		var fn;
		switch (keys.toLowerCase())
			{
				case "set":
					fn = function (a, b)
						{
							if (a.setId > b.setId)
								{return 1;}
							else if (a.setId < b.setId)
								{return -1;}
							return fns[i+1](a, b);
						};
					break;
				case "cost":
					fn = function (a, b)
						{
							if (a.cost > b.cost)
								{return 1;}
							else if (a.cost < b.cost)
								{return -1;}
							return fns[i+1](a, b);
						};
					break;
				case "name":
					fn = function (a, b)
						{
							var tmp = a.name.localeCompare(b.name);
							if (tmp > 0)
								{return 1;}
							else if (tmp < 0)
								{return -1;}
							return fns[i+1](a, b);
						};
					break;
			}
		return fn;
	}
		
function sortCards(cardArray, keys)
	{
		if (typeof(cardArray) == typeof(undefined))
		{
			return;
		}
		var fns = new Array;
		fns[keys.length] = function(a, b) {return 0};
		for (var i = 0; i < keys.length; i++)
			{fns[i] = makeSortFn(keys[i], fns, i);}
		return cardArray.sort(fns[0]);
	}