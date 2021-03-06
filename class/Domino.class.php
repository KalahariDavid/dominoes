<?php 
/**
* Domino Class
*/
class Domino
{
	private $tiles = []; // array with the initial tiles
	private $stock = []; // array with the stock
	private $players = []; // array with the players and their tiles
	private $board = []; // array with the tiles on the board
	private $winner = ''; // filled when there is a winner
	private $withoutStock = []; // quantity of players without stock, 
	private $tie = false; // if $withoutStock equals to the quantity of players, then its a tie

	/**
	 * Domino class constructor
	 *
	 * @param      array    $namePlayers  The name of the players
	 * @param      integer  $maxValue     The maximum value on the tiles
	 * 
	 * @pre $maxValue >= 5
	 */
	function __construct(array $namePlayers = ['Alice', 'Bob'], int $maxValue = 6)
	{
		$this->generateTiles($maxValue);
		$this->draw($this->tiles, $this->stock, ($maxValue+1)*2);

		foreach ($namePlayers as $value) {
			$this->players[$value] = [];
			$this->draw($this->tiles, $this->players[$value], ($maxValue+1));
		}
	}

	/**
	 * Generates the tiles.
	 *
	 * @param      integer  $maxValue  The maximum value on the tiles
	 */
	private function generateTiles(int $maxValue): void
	{
		for ($i=$maxValue; $i >=0; $i--) { 
			for ($j=$i; $j >=0; $j--) { 
				$this->tiles[] = [$i,$j];
			}
		}
		shuffle($this->tiles);
	}

	/**
	 * Adds a tile on the board.
	 *
	 * @param      array   	$source     The source
	 * @param      array  	$target  	The target
	 * @param      integer  $qty     	The quantity of tiles to be drawn
	 *
	 * @return     array   Last drawn tile
	 */
	function draw(array &$source, array &$target, int $qty = 1): array
	{
		if ($qty > count($source)) {
			$qty = count($source);
		}
		for ($i=0; $i < $qty; $i++) { 
			$target[] = array_pop($source);
		}

		return end($target);
	}

	/**
	 * Gets the winner.
	 *
	 * @return     string  The winner.
	 */
	function getWinner(): string
	{
		return $this->winner;
	}

	/**
	 * Gets tie.
	 *
	 * @return     bool Tie.
	 */
	function getTie(): bool
	{
		return $this->tie;
	}

	/**
	 * Check if there is a winner
	 *
	 * @param      string  $name   The name of the player
	 */
	function checkWinner(string $name): void
	{
		if ($this->winner === '' && count($this->players[$name]) === 0) {
			$this->winner = "Player $name has won!";
		}
	}

	/**
	 * Prints the tile
	 *
	 * @param      array   $tile   The tile
	 *
	 * @return     string  Formatted tile
	 */
	function printTile(array $tile): string
	{
		return '<'.implode($tile, ':').'>';
	}

	/**
	 * Prints the board
	 * 
	 * @return string The tiles on the board
	 */ 
	function printBoard(): string
	{
		$board = 'Board is now:<br>';
		foreach ($this->board as $value) {
			$board .= ' '.$this->printTile($value);
		}
		return $board.'.';
	}

	/**
	 * Starts the game.
	 *
	 * @return     string  The initial text
	 */
	function startGame(): string
	{
		$tile = $this->draw($this->stock, $this->board, 1);
		return 'Game starting with first tile: '.$this->printTile($tile);
	}

	/**
	 * Adds a tile on the board.
	 *
	 * @param      string   $name     The name of the player
	 * @param      integer  $tileKey  The tile key
	 * @param      array    $tile     The tile
	 * @param      string   $side     The side
	 *
	 * @return     array   Status of the board after the playing
	 */
	function addTileOnTheBoard(string $name, int $tileKey, array $tile, string $side): array
	{
		switch ($side) {
			case 'left':
				unset($this->players[$name][$tileKey]);
				$left = $this->board[0];
				array_unshift($this->board, $tile);
				$this->checkWinner($name);
				return [
					true, 
					'domino',
					sprintf('%s plays %s to connect to tile %s on the board.', 
						$name, 
						$this->printTile($tile), 
						$this->printTile($left)
					)
				];

			case 'right':
				unset($this->players[$name][$tileKey]);
				$right = end($this->board);
				$this->board[] = $tile;
				$this->checkWinner($name);
				return [
					true, 
					'domino',
					sprintf('%s plays %s to connect to tile %s on the board.', 
						$name, 
						$this->printTile($tile), 
						$this->printTile($right)
					)
				];
			
			default:
				return [false, 'error', 'Oops. =/'];
		}
	}

	/**
	 * Draws a tile from stock.
	 *
	 * @param      string  $name   The name of the player
	 *
	 * @return     array   array Status of the board after the playing
	 */
	function drawFromStock(string $name): array
	{
		$hasStock = count($this->stock) === 0 ? false : true;
		if (!$hasStock) {
			$this->withoutStock[$name] = true;
			if (count($this->withoutStock) === count($this->players)) { // checks if it is a tie
				$this->tie = true;
				return [
					true, 
					'tie',
					'Nobody can play. It is a tie!!'
				];
			}
			return [
					true, 
					'without-stock',
					sprintf('Without stock! %s cannot play.', $name)
				];
		}

		$tile = $this->draw($this->stock, $this->players[$name], 1);
		reset($this->players[$name]);
		array_reverse($this->players[$name]);

		return [
			false, 
			'draw',
			sprintf('%s cannot play, drawing tile %s.', 
				$name, 
				$this->printTile($tile)
			)
		];
	}

	/**
	 * Try to find tile on the left side of the board
	 *
	 * @param      integer  $left   The left
	 * @param      array  	$value  The value
	 * @param      string   $name   The name
	 * @param      integer  $key    The key
	 *
	 * @return     array    Status of the board after the playing
	 */
	private function tryLeftSideBoard(int $left, array $value, string $name, int $key): array
	{
		// try LEFT side
		$foundLeft = array_search($left, $value);
		
		if ( $foundLeft === 1) { // add the current tile on the left side
			return $this->addTileOnTheBoard($name, $key, $value, 'left');;
		}
		if ($foundLeft === 0) { // invert the current tile and then add on the left side
			return $this->addTileOnTheBoard($name, $key, array_reverse($value), 'left');;
		}

		return [];
	}

	/**
	 * Try to find tile on the right side of the board
	 *
	 * @param      integer  $right   The right
	 * @param      array  	$value  The value
	 * @param      string   $name   The name
	 * @param      integer  $key    The key
	 *
	 * @return     array    Status of the board after the playing
	 */
	private function tryRightSideBoard(int $right, array $value, string $name, int $key): array
	{
		// try RIGHT side
		$foundRight = array_search($right, $value);
		
		if ( $foundRight === 0) { // add the current tile on the right
			return $this->addTileOnTheBoard($name, $key, $value, 'right');;
		}
		if ($foundRight === 1) { // invert the current tile and then add on the right side
			return $this->addTileOnTheBoard($name, $key, array_reverse($value), 'right');;
		}

		return [];
	}

	/**
	 * Plays
	 *
	 * @param      string  $name   The name
	 *
	 * @return     array Status of the board after the playing
	 */
	function play(string $name): array
	{
		if ($this->getWinner() !== '' || $this->getTie()) {
			return [true, '', ''];
		}

		$left = $this->board[0][0];
		$right = end($this->board)[1];

		// check if the player's tiles match with one of the tiles of the board
		foreach ($this->players[$name] as $key => $value) {
			if (rand(0,1) === 0) {
				$result = $this->tryLeftSideBoard($left, $value, $name, $key);
				if ($result[0]) {
					return $result;
				}
				$result = $this->tryRightSideBoard($right, $value, $name, $key);
				if ($result[0]) {
					return $result;
				}
			} else {
				$result = $this->tryRightSideBoard($right, $value, $name, $key);
				if ($result[0]) {
					return $result;
				}
				$result = $this->tryLeftSideBoard($left, $value, $name, $key);
				if ($result[0]) {
					return $result;
				}
			}
		}

		// else draw from the stock
		return $this->drawFromStock($name);
	}

}
?>
