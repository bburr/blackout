# Blackout

This is a web app for a card game called Blackout.

## Game Rules

Blackout is a card game that can be played by 2 to 7 players with a standard 52-card deck. 
The Ace is the highest card, followed by the King, all the way down to the 2.

### Dealing Cards
The first dealer is determined in this app by a 1-20 RNG roll. The game is played in multiple "rounds", with each round containing one or more "tricks".
The first round starts with each player being dealt 1 card, up to a maximum number of rounds based on number of players.
After the round with the highest number of cards, the number of cards per hand descends until the game ends with a round of 1 card per hand.
The dealer always deals first to the player to the left (clockwise). The app handles this by iterating through an array of players.

### Trump Suit
After the players have been dealt their hands, another card is revealed from the top of the deck to determine the trump suit.
This trump suit will be used when determining the winner of a trick below.

### Betting
At the beginning of each round, each player (starting with the player to the dealer's left) makes a bet on the number of tricks they will win in that round.
Each player can bet a minimum of 0 and a maximum of the number of tricks in the round, equal to the number of cards in their hand.

### Play of each trick
Once betting is complete, the player to the dealer's left starts the first trick by playing any card from their hand.
For this trick, all subsequent players must play a card matching the first card's suit if they have one. 
If they do not have a card of matching suit, they can play any card from their hand.

### Winning the trick
The highest card played of the trump suit will win a trick. If no card of the trump suit is played, the highest card matching the suit of the first card played wins the trick.
Some rounds can be played without a trump card, in which case the highest card matching the suit of the first card played wins the trick.
The winner of a trick will play the first card of the next trick within the same round.

### Round Scoring
When all tricks in a round have been played, the round is scored. Players whose bet matched their actual number of tricks won earn 10 points plus their bet.
Players whose bet did not match receive 0 points for the round.

### Game Winner
The winner is the player with the most points after all rounds have been played.

todo: tie breaker

## Rule Configurations

The following aspects of the rules are intended to be configurable via the game settings:
* Starting number of tricks (must be <= max number of tricks)
* Maximum number of tricks
* Ending number of tricks (must be <= max number of tricks)
* Points for correct bet
* (Configurable per round) Draw a trump card for the round [NYI]

## Artisan Commands
* `swoole:http start` - Start the Swoole HTTP server
* `state:display {gameId}` - This will display various information about the state of the given game
* `game:quickstart` - This will start a game and make bets for the first round, then output the game ID

## Technologies in Use
* [PHP 8.1](https://www.php.net/)
* [Laravel 9](https://laravel.com/)
* [Swoole](https://www.swoole.com/)
* [Laravel Jetstream](https://jetstream.laravel.com/)
* [Inertia](https://inertiajs.com/)
* [React](https://reactjs.org/)
* [Vite](https://vitejs.dev/)
* [Tailwind](https://tailwindcss.com/)

## Rules References
* http://www.catsatcards.com/Games/Blackout.html
* https://www.classicgamesandpuzzles.com/Blackout.html
