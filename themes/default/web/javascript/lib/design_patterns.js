/**
 * Array List
 * @return
 */
function ArrayList () {
   this.aList = []
};
        
ArrayList.prototype.count = function () {
   return this.aList.length;
};
        
ArrayList.prototype.add = function (object) {
   return this.aList.push(object);
};

ArrayList.prototype.getAt = function (index) {
   if (index > -1 && index < this.aList.length) {
      return this.aList[index];
   } else {
      return undefined;
   }
};
        
ArrayList.prototype.clear = function () {
   this.aList = [];
};

ArrayList.prototype.removeAt = function (index) {
   var m_count = this.aList.length;
            
   if (m_count > 0 && index > -1 && index < this.aList.length) {
        switch (index){
        case 0:
            this.aList.shift();
            break;
        case m_count - 1:
            this.aList.pop();
            break;
        default:
            var head = this.aList.slice(0, index);
            var tail = this.aList.slice(index + 1);
            this.aList = head.concat(tail);
            break;
        }
   }
};

ArrayList.prototype.insert = function (object, index) {
   var m_count = this.aList.length;
   var m_returnValue = -1;

   if (index > -1 && index <= m_count) {
        switch (index){
        case 0:
            this.aList.unshift(object);
            m_returnValue = 0;
            break;
        case m_count:
            this.aList.push(object);
            m_returnValue = m_count;
            break;
        default:
            var head = this.aList.slice(0, index - 1);
            var tail = this.aList.slice(index);
            this.aList = this.aList.concat(tail.unshift(object));
            m_returnValue = index;
        }
   }
            
   return m_returnValue;
};

ArrayList.prototype.indexOf = function (object, startIndex) {
   var m_count = this.aList.length;
   var m_returnValue = - 1;
            
   if (startIndex > -1 && startIndex < m_count) {
      var i = startIndex;

      while (i < m_count) {
         if (this.aList[i] == object) {
            m_returnValue = i;
            break;
         }
                    
         i++;
      }
   }
            
   return m_returnValue;
};
            
ArrayList.prototype.lastIndexOf = function (object, startIndex) {
   var m_count       = this.aList.length;
   var m_returnValue = - 1;
            
   if (startIndex > -1 && startIndex < m_count) {
      var i = m_count - 1;
                
      while (i >= startIndex) {
         if (this.aList[i] == object) {
            m_returnValue = i;
            break;
         }
                    
         i--;
      }
   }
            
   return m_returnValue;
};

/**
 * Observer
 * @return
 */
function Observer () {
   this.update = function () {
      return;
   }
};

/**
 * Subject
 * @return
 */
function Subject () {
   this.observers = new ArrayList();
};

Subject.prototype.notify = function (context) {
   var m_count = this.observers.count();
            
   for (var i = 0; i < m_count; i++) {
      this.observers.getAt(i).update(context);
   }
};

Subject.prototype.addObserver = function (observer) {
   if (!observer.update) {
      throw 'Wrong parameter';
   }

   this.observers.add(observer);
};

Subject.prototype.removeObserver = function (observer) {
   if (!observer.update) {
      throw 'Wrong parameter';
   }
   
   this.observers.removeAt(this.observers.indexOf(observer, 0));
};

/**
 * Inherit helper
 */
function inherits (base, extension) {
   for (var property in base) {
      try {
         extension[property] = base[property];
      }
      catch (warning) {}
   }
}