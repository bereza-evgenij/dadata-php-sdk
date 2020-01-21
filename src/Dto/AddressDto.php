<?php

namespace Bereza\DaData\Dto;

/**
 * Класс-модель для сущности "Адрес"
 * Class AddressDto
 * @package Bereza\DaData\Dto
 *
 * @property string $postal_code - почтовый индекс
 *
 * @property string $region_fias_id - код ФИАС региона
 * @property string $region_with_type - регион с типом
 *
 * @property string $area_fias_id - код ФИАС района в регионе
 * @property string $area_with_type - район в регионе с типом
 *
 * @property string $city_fias_id - код ФИАС города
 * @property string $city_with_type - город с типом
 *
 * @property string $settlement_fias_id - код ФИАС населенного пункта
 * @property string $settlement_with_type - населенный пункт с типом
 *
 * @property string $street_with_type - улица с типом
 * @property string $house_type - тип дома (сокращенный)
 * @property string $house - дом
 * @property string $block_type - корпуса/строения (сокращенный)
 * @property string $block - корпус/строение
 * 
 * @property string $capital_marker - признак центра района или региона
 *
 * @property float $geo_lat - широта
 * @property float $geo_lon - долгота
 */
class AddressDto extends AbstractEntityDto
{
    /**
     * Центр района (Московская обл, Одинцовский р-н, г Одинцово)
     */
    const CAPITAL_MARKER_AREA_CENTER = 1;
    
    /**
     * Центр региона (Новосибирская обл, г Новосибирск)
     */
    const CAPITAL_MARKER_REGION_CENTER = 2;
    
    /**
     * Центр района и региона (Костромская обл, Костромской р-н, г Кострома)
     */
    const CAPITAL_MARKER_AREA_AND_REGION_CENTER = 3;
    
    /**
     * Ни то, ни другое (Московская обл, г Балашиха)
     */
    const CAPITAL_MARKER_NONE = 0;
    
    /**
     * Получить код ФИАС населенного пункта или города
     * @return string
     */
    public function getSettlementCityFiasId(): string
    {
        return $this->settlement_fias_id ? : $this->city_fias_id;
    }
    
    /**
     * Получить населенный пункт или город с типом
     * @return string
     */
    public function getSettlementCityWithType(): string
    {
        return $this->settlement_with_type ? : $this->city_with_type;
    }
    
    /**
     * Получить дом с типом
     * @return string
     */
    public function getHouseWithType(): string
    {
        return join(' ', array_filter([$this->house_type, $this->house]));
    }
    
    /**
     * Получить корпус, строение с типом
     * @return string
     */
    public function getBlockWithType(): string
    {
        return join(' ', array_filter([$this->block_type, $this->block]));
    }
    
    /**
     * Получить дом, корпус, строение с типом
     * @return string
     */
    public function getHouseBlockWithType(): string
    {
        return $this->getHouseWithType() ? : $this->getBlockWithType();
    }
    
    /**
     * Является центром района (Московская обл, Одинцовский р-н, г Одинцово)
     * @return bool
     */
    public function isAreaCenter(): bool
    {
        return $this->capital_marker == static::CAPITAL_MARKER_AREA_CENTER;
    }
    
    /**
     * Является центром региона (Новосибирская обл, г Новосибирск)
     * @return bool
     */
    public function isRegionCenter(): bool
    {
        return $this->capital_marker == static::CAPITAL_MARKER_REGION_CENTER;
    }
    
    /**
     * Является центром района и региона (Костромская обл, Костромской р-н, г Кострома)
     * @return bool
     */
    public function isAreaAndRegionCenter(): bool
    {
        return $this->capital_marker == static::CAPITAL_MARKER_AREA_AND_REGION_CENTER;
    }
    
    /**
     * Не является ни центром района, ни региона (Московская обл, г Балашиха)
     * @return bool
     */
    public function isNoCenter(): bool
    {
        return $this->capital_marker == static::CAPITAL_MARKER_NONE;
    }
}
