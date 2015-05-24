/*
  https://github.com/Torsten-/zahlenschloss
  
  Copyright (C) 2015 Zahlenschloss
  Torsten Amshove <torsten@amshove.net>
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>
#include <SoftwareSerial.h>

///////////////////////
//// Configuration ////
///////////////////////
#define BACKLIGHT_DELAY 10     // after X seconds the display backlight turns off
#define PIN_LENGTH 6           // length of the Pin-Code
#define DEBUG_TO_SERIAL       // show messages on serial interface
#define SSID ""
#define PASS ""
#define IP ""
String URL = "GET /?pin=";

/////////////////////////////////////
//// Initialise global variables ////
/////////////////////////////////////
// Keypad
#define KEYPAD_ROWS 4      // Number of Rows of the Keypad
#define KEYPAD_COLS 3      // Number of Cols of the Keypad
char keys[KEYPAD_ROWS][KEYPAD_COLS] = { // Keypad Characters
  {'1','2','3'},
  {'4','5','6'},
  {'7','8','9'},
  {'*','0','#'}
};
byte rowPins[KEYPAD_ROWS] = {10, 9, 8, 7}; //connect to the row pinouts of the keypad
byte colPins[KEYPAD_COLS] = {4, 5, 6};     //connect to the column pinouts of the keypad
Keypad keypad = Keypad( makeKeymap(keys), rowPins, colPins, KEYPAD_ROWS, KEYPAD_COLS );

// WLAN
SoftwareSerial ESPserial(2, 3); // RX | TX

// LCD
LiquidCrystal_I2C lcd(0x27, 2, 1, 0, 4, 5, 6, 7, 3, POSITIVE);  // Set the LCD I2C address
uint8_t pin_lcd_offset = 5; // Offset to move PIN to the right place in the display

// Pincode
char pin[PIN_LENGTH];
uint8_t pinCounter = 0;
bool displayOff = false;
long lastBacklightOn = 0;

///////////////
//// Setup ////
///////////////
void setup() {
  // Debugging
  #ifdef DEBUG_TO_SERIAL
    Serial.begin(9600);
    Serial.println("Welcome to Zahlenschloss - debugging enabled ..");
  #endif
  
  // Init LCD
  lcd.begin(16,2);
  lcd.backlight();
  lastBacklightOn = millis();
 
  // Init WLAN
  ESPserial.begin(9600);
  ESPserial.println("AT");  
  delay(5000);
  if(ESPserial.find("OK")){
    if(connectWiFi()){
      #ifdef DEBUG_TO_SERIAL
        Serial.println("WLAN connected");
      #endif
      lcd.setCursor(0,0);
      lcd.print("WLAN            ");
      lcd.setCursor(0,1);
      lcd.print("connected       ");
    }else{
      lcd.setCursor(0,0);
      lcd.print("ERROR: WLAN     ");
      lcd.setCursor(0,1);
      lcd.print("not connected   ");
      #ifdef DEBUG_TO_SERIAL
        Serial.println("WLAN not connected ..");
        Serial.println("-- boot stopped --");
      #endif
      while(true){ delay(50000); } // Don't boot any further
    }
  }else{
    lcd.setCursor(0,0);
    lcd.print("ERROR:WLAN-Modul");
    lcd.setCursor(0,1);
    lcd.print("not repsonding  ");
      #ifdef DEBUG_TO_SERIAL
        Serial.println("WLAN Module not repsonding ..");
        Serial.println("-- boot stopped --");
      #endif
    while(true){ delay(50000); } // Don't boot any further
  }
  
  // Reset LCD and Pin
  reset();
}

//////////////
//// Loop ////
//////////////
void loop() {
  // Turn of Display and reset LCD and Pin on Timeout
  if((millis()-lastBacklightOn)/1000 > BACKLIGHT_DELAY){
    reset();
    lcd.noBacklight();
    displayOff = true;
  }
  
  // Get Inputs from Keypad
  char key = keypad.getKey();
  if (key != NO_KEY){
    lastBacklightOn = millis(); // Reset timer for Timeout
    if(displayOff){
      // If display was off turn on
      lcd.backlight();
      displayOff = false;
    }
    
    if(key != '*' && key != '#'){ // Ignore * and # as Input
      // Remember given character to build whole pin before sending
      lcd.setCursor(pin_lcd_offset+pinCounter,1);
      lcd.print("*");
      pin[pinCounter] = key;
      pinCounter++;
        
      // Pin length reached
      if(pinCounter == PIN_LENGTH){
//      if(key == '*' || key == '#' || pinCounter == PIN_LENGTH){ // alternative method to send the pin also by pressing * or #
        // Send pin and reset LCD and Pin
        sendPin();
        reset();
      }
    }
  }
}

/////////////
// sendPin //
/////////////
// This function sends the Pin to the server
void sendPin(){
  // Update LCD
  lcd.setCursor(0,1);
  lcd.print("Pruefe PIN ..   ");
  
  // Build String out of pin
  String pinString;
  for(uint8_t i=0; i<16; i++){
    if(pin[i] > 47 && pin[i] < 58){
      pinString += pin[i];
    }else break;
  }
  
  #ifdef DEBUG_TO_SERIAL
    Serial.println("Pin entered:");
    Serial.println(pinString);
  #endif
  
  ////////////////////
  // Send PIN via WLAN
  ////////////////////
  // Open Connection to Server
  String cmd = "AT+CIPSTART=\"TCP\",\"";
  cmd += IP;
  cmd += "\",80";
  #ifdef DEBUG_TO_SERIAL
    Serial.println("CMD: "+cmd);
  #endif
  ESPserial.println(cmd);
  delay(5000);
  if(ESPserial.find("Error")){
    #ifdef DEBUG_TO_SERIAL
      Serial.println("ERROR: Connection to Server not established");
    #endif
    
    // Update LCD
    lcd.setCursor(0,1);
    lcd.print("techn. Fehler #1");
    delay(5000);
    
    return;
  }
  while(ESPserial.available()){ // Workaround to clear buffer
    ESPserial.read();
  }
  
  // Send GET-Request
  cmd = URL;
  cmd += pinString;
  ESPserial.print("AT+CIPSEND=");
  ESPserial.println(cmd.length()+2); // +2 for CR+LF

  if(ESPserial.find(">")){
    #ifdef DEBUG_TO_SERIAL
      Serial.println("URL: "+cmd);
    #endif
    
    // Send command with URL, clear serial buffer and send CR+LF to execute the command
    ESPserial.print(cmd);
    while(ESPserial.available()){ // Workaround to clear buffer
      ESPserial.read();
    }
    ESPserial.println();
    delay(2000);
    
    String responseBuffer = "";
    while(ESPserial.available()){
      char character = ESPserial.read();
      responseBuffer.concat(character);
    }
    
    #ifdef DEBUG_TO_SERIAL
      Serial.println("Response:");
      Serial.println(responseBuffer);
    #endif

    // Check Result
    String result = responseBuffer.substring(responseBuffer.indexOf("=")+1,responseBuffer.indexOf("=")+2);
    if(result == "o"){
      #ifdef DEBUG_TO_SERIAL
        Serial.println("PIN accepted - door opened");
      #endif
      
      // Update LCD
      lcd.setCursor(0,1);
      lcd.print("Tuer geoeffnet  ");
      delay(5000);
    }else if(result == "c"){
      #ifdef DEBUG_TO_SERIAL
        Serial.println("PIN accepted - door closed");
      #endif
      
      // Update LCD
      lcd.setCursor(0,1);
      lcd.print("Tuer geschlossen");
      delay(5000);
    }else{
      #ifdef DEBUG_TO_SERIAL
        Serial.println("PIN declined - or some confusing answere from backend");
        Serial.print("Result: ");
        Serial.println(result);
      #endif
      
      // Update LCD
      lcd.setCursor(0,1);
      lcd.print("PIN falsch      ");
      delay(5000);
    }
    
    // Close connection
    ESPserial.println("AT+CIPCLOSE");
  }else{
    ESPserial.println("AT+CIPCLOSE");

    #ifdef DEBUG_TO_SERIAL
      Serial.println("ERROR: AT+CIPSEND failed");
    #endif
    
    // Update LCD
    lcd.setCursor(0,1);
    lcd.print("techn. Fehler #2");
    delay(5000);
  }
}

///////////
// reset //
///////////
// Reset the LCD and the Pin
void reset(){
  for(uint8_t i=0; i < PIN_LENGTH; i++) pin[i] = 10; // Reset pin-buffer
  pinCounter = 0;                                    // Reset pin-counter
  
  // Reset LCD
  lcd.setCursor(0,0);
  lcd.print("Willkommen");
  lcd.setCursor(0,1);
  lcd.print("PIN: ");
  lcd.setCursor(pin_lcd_offset,1);
  lcd.print("                ");
}

/////////////////
// connectWiFi //
/////////////////
// Connect to the WLAN
boolean connectWiFi(){
  // Set Mode to Client
  ESPserial.println("AT+CWMODE=1");
  delay(2000);
  while(ESPserial.available()){ // Workaround to clear buffer
    ESPserial.read();
  }
  
  // Connect with SSID & Key
  String cmd = "AT+CWJAP=\"";
  cmd += SSID;
  cmd += "\",\"";
  cmd += PASS;
  cmd += "\"";
  #ifdef DEBUG_TO_SERIAL
    Serial.println("CMD: "+cmd);
  #endif
  ESPserial.println(cmd);
  while(ESPserial.available()){ // Workaround to clear buffer
    ESPserial.read();
  }
  delay(5000);
 
  // Check result
  if(ESPserial.find("OK")){
    return true;
  }else{
    return false;
  }
}
