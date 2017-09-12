// Counts the non-undefined values of the array
Array.prototype.count = function () {
    var count = 0;

    this.forEach(function (t) {
        if (t !== undefined) count++;
    });

    return count;
};

// Gets the last element in the array. Undefined aware.
Array.prototype.last = function () {
    for (var i = this.length - 1; i >= 0; i--) {
        if (this[i] !== undefined) {
            return this[i];
        }
    }

    return undefined;
};

// Moves element of an array to a new position
Array.prototype.move = function (old_index, new_index) {
    if (new_index >= this.length) {
        var k = new_index - this.length;
        while ((k--) + 1) {
            this.push(undefined);
        }
    }
    this.splice(new_index, 0, this.splice(old_index, 1)[0]);
    return this; // for testing purposes
};

