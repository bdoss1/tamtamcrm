<?php
class DateParser {
    public $variables = [];

    public function evaluate($string)
    {
       $this->variables = ['MONTH' => 'm'];

        $stack = $this->parse($string);
        
        return $this->run($stack);
    }

    public function parse($string)
    {
        $tokens = $this->tokenize($string);
        
        $output = new Stack();
        $operators = new Stack();
        foreach ($tokens as $token) {
            $token = $this->extractVariables($token);
            
            //$expression = TerminalExpression::factory($token);
            if (in_array($token, ['+', '-', '/', '*'])) {
            
                $this->parseOperator($token, $output, $operators);
            } elseif ($this->isParenthesis($token)) {
                $this->parseParenthesis($token, $output, $operators);
            } else {
                $output->push($token);
            }
        }
        while (($op = $operators->pop())) {
            /* if ($op->isParenthesis()) {
                throw new \RuntimeException('Mismatched Parenthesis');
            } */
            $output->push($op);
        }

        return $output;
    }

    protected function isParenthesis($expression) {
        return $expression === 'to';
    }

    public function registerVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function run(Stack $stack)
    {
        
        while (($operator = $stack->pop()) && in_array($operator, ['+', '-', '/', '*'])) {
        
        
            $numerator = $stack->pop();
            $type = $stack->pop();
        
            $date = date('Y-m-d');
            $date = new DateTime($date);
        
            switch($type) {
                case 'm':
            
                $date = $date->modify($operator . $numerator . 'months');
                break;
            }
        
            $value = $date->format('Y-m-d');
       
            if (!empty($value)) {
                $stack->push($value);
            }
            
           
           break;
           
        }
        
        return $this->render($stack);
    }   

    protected function extractVariables($token)
    {
        if ($token[0] == '$') {
            $key = substr($token, 1);

            return isset($this->variables[$key]) ? $this->variables[$key] : 0;
        }

        return $token;
    }
      
    protected function render(Stack $stack)
    {
        $output = '';
        while (($el = $stack->shift())) {
           
           $output .= ' ' . $el;
           
        }
        if ($output) {
            return $output;
        }
        throw new \RuntimeException('Could not render output');
    }

    protected function parseParenthesis($expression, Stack $output, Stack $operators)
    {
        $type = $output->pop();
    
        $output->push(date($type));
        $output->push($expression);
    
    
        /* if ($expression->isOpen()) {
            $operators->push($expression);
        } else {
            $clean = false;
            while (($end = $operators->pop())) {
                if ($end->isParenthesis()) {
                    $clean = true;
                    break;
                } else {
                    $output->push($end);
                }
            }
            if (!$clean) {
                throw new \RuntimeException('Mismatched Parenthesis');
            }
        } */
    }

    protected function parseOperator($expression, Stack $output, Stack $operators)
    {
    
        $end = $operators->poke();
       
        if (!$end) {
            $operators->push($expression);
        } elseif (in_array($end, ['+', '-', '/', '*'])) {
            do {
                
                    $output->push($operators->pop());
                
            } while (($end = $operators->poke()) && in_array($end, ['+', '-', '/', '*']));
            $operators->push($expression);
        } else {
            $operators->push($expression);
        }
      
    }

    protected function tokenize($string)
    {
        $parts = preg_split('((\d+\.?\d+|\+|-|\(|\)|\*|/)|\s+)', $string, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $parts = array_map('trim', $parts);
        foreach ($parts as $key => &$value) {
            //if this is the first token or we've already had an operator or open paren, this is unary
            if ($value == '-') {
                if ($key - 1 < 0 || in_array($parts[$key - 1], array('+', '-', '*', '/', '('))) {
                    $value = 'u';
                }
            }
        }

        return $parts;
    }
}

class Stack
{
    protected $data = array();

    public function push($element)
    {
        $this->data[] = $element;
    }

    public function poke()
    {
        return end($this->data);
    }

    public function pop()
    {
        return array_pop($this->data);
    }
    
    public function shift()
    {
        return array_shift($this->data);
    }

    //check out the end of the array without changing the pointer via http://stackoverflow.com/a/7490837/706578
    public function peek()
    {
        return current(array_slice($this->data, -1));
    }
    
    public function count()
    {
        return count($this->data);
    }
}
