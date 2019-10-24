# Changes in fork

Macro was rewritten in order to accept variables as a valid parameter.

Due to nature of latte macros, parameter was evaluated as string.

For that purpose, the SVG processing was moved into a static method and macro itself only calls this method.

## Additional changes

Added used ext-libxml and nette/di into composer.json, applied coding standards and changed namespace