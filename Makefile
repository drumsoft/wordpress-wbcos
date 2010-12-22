#wordpress wbcos

ARCHIVER = zip
FILES = wbcos/wbcos.js wbcos/wbcos.php
TARGET = wbcos.zip


all : $(TARGET)

$(TARGET) : $(FILES)
	$(ARCHIVER) $(TARGET) $(FILES)

clean:
	rm $(TARGET)
