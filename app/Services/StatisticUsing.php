<?php

namespace App\Services;

use Illuminate\Database\Connection;
use App\Services\StatisticAbstract;

class StatisticUsing extends StatisticAbstract
{

    protected $db;
    
    public $fromMonth;
    public $toMonth;
    public $fromYear;
    public $toYear;
    
    public function __construct(Connection $db, \Illuminate\Validation\Validator $validator)
    {
        parent::__construct($db, $validator);
        $this->validator->setRules([
            'fromMonth' => 'nullable|integer|min:1|max:12',
            'toMonth' => 'nullable|integer|min:1|max:12|gte:fromMonth',
            'fromYear' => 'nullable|integer|required_with:fromMonth|min:1970|max:' . date('Y'),
            'toYear' => 'nullable|integer|required_with:toMonth|gte:fromYear|min:1970|max:' . date('Y'),
        ]);

    }
    
    public function validate()
    {
        $this->validator->setData([
            'fromMonth' => $this->fromMonth,
            'toMonth' => $this->toMonth,
            'fromYear' => $this->fromYear,
            'toYear' => $this->toYear
        ]);
        if ($this->validator->fails()) {
            throw new \InvalidArgumentException(implode(', ', $this->validator->errors()->all()));
        }
        return true;
    }
    
    public function getData() : array
    {
        $this->validate();
        $where = [];
        if (!empty($this->fromYear)) {
            $this->fromMonth = $this->fromMonth ?? 1;
            $where[] = "(MONTH(journal.created_at) >= {$this->fromMonth} AND YEAR(journal.created_at) >= {$this->fromYear})";
        }
        if (!empty($this->toYear)) {
            $this->toMonth = $this->toMonth ?? 12;
            $where[] = "(MONTH(journal.created_at) <= {$this->toMonth} AND YEAR(journal.created_at) <= {$this->toYear})";
        }
        $query = $this->db->table('journal')->selectRaw('count(book_id) as cnt, MONTH(created_at) as mn, YEAR(created_at) as ya')
                ->groupBy($this->db->raw('mn, ya'))
                ->orderBy($this->db->raw('ya, mn'));
        if (!empty($where)) {
            $query->whereRaw(implode(' AND ', $where));
        }
        
        $monthData = $query->get()->all();
        if (empty($monthData) && empty($this->year)) {
            return [[
                'value' => 0
            ]];
        }
        
        $yearStart = $this->fromYear ?? $monthData[0]->ya;
        $yearEnd = $this->toYear ?? $monthData[count($monthData) -1]->ya;
        $monthStart = $this->fromMonth ?? $monthData[0]->mn;
        $monthEnd = $this->toMonth ?? $monthData[count($monthData) -1]->mn;
        $result = [];
        foreach (range($yearStart, $yearEnd) as $year) {
            $yearStat = $this->getYearData($year, $monthStart, ($year === $yearEnd ? $monthEnd : 12));
            $result = array_merge($result, $yearStat);
            $monthStart = 1;
        }
        return $result;
    }
    
    private function getYearData(int $year, int $monthStart, int $monthEnd) : array
    {
        $yearData = $this->db->table('journal')
                ->selectRaw('count(book_id) as cnt, YEAR(created_at) as ya')
                ->where($this->db->raw('YEAR(created_at)'), '=', $year)
                ->groupBy($this->db->raw('ya'))
                ->get()
                ->shift();
        
        $result  = [];
        foreach (range($monthStart, $monthEnd) as $month) {
            $monthData = $this->getMonthData($month, $year);
            $result = array_merge($result, $monthData);
        }
        $result[] = [
            'date' => $year,
            'value' => $yearData->cnt
        ];
        return $result;        
    }
    
    private function getMonthData(int $month, int $year) : array
    {
        $monthData = $this->db->table('journal')
                ->selectRaw('count(book_id) as cnt')
                ->where([
                        [$this->db->raw('YEAR(created_at)'), '=', $year],
                        [$this->db->raw('MONTH(created_at)'), '=', $month],
                    ])
                ->groupBy($this->db->raw('MONTH(created_at)'))
                ->get()
                ->shift();
        $result = [];
        $statData = $this->db->table('journal')
                ->selectRaw('book_id, count(book_id) as cnt')
                ->where([
                    [$this->db->raw('YEAR(created_at)'), '=', $year],
                    [$this->db->raw('MONTH(created_at)'), '=', $month],
                ])
                ->groupBy($this->db->raw('book_id, MONTH(created_at)'));
        $monthStat = $this->db->table('books')
                ->selectRaw('title, stat_data.cnt')
                ->leftJoinSub($statData, 'stat_data', function($join){
                    $join->on('books.id', '=', 'stat_data.book_id');
                })
                ->get();
        foreach($monthStat as $stat) {
            $result[] = [
                'date' => $year . '-' . $month,
                'title' => $stat->title,
                'value' => (int)$stat->cnt
            ];
        }
        $result[] = [
            'date' => $year . '-' . $month,
            'value' => $monthData->cnt
        ];
        return $result;
    }
}
