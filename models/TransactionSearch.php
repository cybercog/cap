<?php

namespace amilna\cap\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use amilna\cap\models\Transaction;

/**
 * TransactionSearch represents the model behind the search form about `amilna\cap\models\Transaction`.
 */
class TransactionSearch extends Transaction
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type', 'isdel'], 'integer'],
            [['total','subject', 'reference','tags','title', 'remarks', 'time'], 'safe'],            
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
	
	private function queryNumber($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$number = explode(" ",$this->$field);			
				if (count($number) == 2)
				{									
					array_push($params,[$number[0], ($tab?$tab.".":"").$field, $number[1]]);	
				}
				elseif (count($number) > 2)
				{															
					array_push($params,['>=', ($tab?$tab.".":"").$field, $number[0]]);		
					array_push($params,['<=', ($tab?$tab.".":"").$field, $number[0]]);		
				}
				else
				{					
					array_push($params,['=', ($tab?$tab.".":"").$field, str_replace(["<",">","="],"",$number[0])]);		
				}									
			}
		}	
		return $params;
	}
	
	private function queryTime($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$time = explode(" - ",$this->$field);			
				if (count($time) > 1)
				{								
					array_push($params,['>=', "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);	
					array_push($params,['<=', "concat('',".($tab?$tab.".":"").$field.")", $time[1]." 24:00:00"]);
				}
				else
				{
					if (substr($time[0],0,2) == "< " || substr($time[0],0,2) == "> " || substr($time[0],0,2) == "<=" || substr($time[0],0,2) == ">=") 
					{					
						array_push($params,[str_replace(" ","",substr($time[0],0,2)), "concat('',".($tab?$tab.".":"").$field.")", trim(substr($time[0],2))]);
					}
					else
					{					
						array_push($params,['like', "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);
					}
				}	
			}
		}	
		return $params;
	}
	
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Transaction::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,            
            'type' => $this->type,            
            'isdel' => $this->isdel,
        ]);
        
        
        $params = self::queryNumber([['total']]);		
		foreach ($params as	$p)
		{
			$query->andFilterWhere($p);
		}	
		
        $params = self::queryTime([['time']]);		
		foreach ($params as	$p)
		{
			$query->andFilterWhere($p);
		}	
        
        $params = self::queryString([['subject'],['title'],['reference'],['tags'],['remarks']]);		
		foreach ($params as	$p)
		{
			$query->andFilterWhere($p);
		}	        

        //$query->andFilterWhere(['like', 'lower(subject)', strtolower($this->subject)]);
            
        return $dataProvider;
    }
}
