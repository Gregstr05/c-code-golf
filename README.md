# c code golf
A website checker for simplified version of code golf in C programming language

This website is deployed [here](https://golf.gregstr.eu)
> [!IMPORTANT]
> This project got created for a very specific personal purpose. All the coding problems are the group's university's *Introduction to programming* class home work assignments. We do not share solutions between ourselves, but we're interested in adding something more, almost ezoteric, to our assignments in the form of this "code golf" challenge and this tool got created to enable us to keep a central scoring system and automatically score our code without relying on counting it manually or running some command line tool and entering the result in a spreadsheet manually

## Rules
The most significant difference compared to the typical code golf is that we don't count characters, nor do we count lines, but we decided to instead count "statements"

> [!NOTE]
> Most of the rules were decided on because we think that formatting shouldn't affect the creativity in solving specific coding problems


### What gets ignored:
- Blank lines
- Comments
- Openning or closing braces of any type (if on a new line)
- `#include` statements
- Function definitions

### What counts:
- Any line that (should) end in a semicolon `;`
- Any other preprocessor directive
- Conditional statements (if, while, do, for)
    - `for` loops get counted as 1 statement


### Example:
``` C
#include <stdio.h>

void SayGoodbye(const char* name);

int main() 
{
    // Say goodbye to World
    SayGoodbye("World");
    return 0;
}

// Prints "Goodbye {name}!" into stdout
void SayGoodbye(const char* name) {
    printf("Goodbye %s!", name);
}
```
The above code would be counted as **5 statements**.

This is how the code would look if we deleted all that get's ignored:

``` C
int main() {
    SayGoodbye("World");
    return 0;}
void SayGoodbye(const char* name) {
    printf("Goodbye %s!", name);}
```
