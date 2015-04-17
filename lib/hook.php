<?php

/**
 * This class manages the hooks. It basically provides two functions: adding
 * slots and emitting signals.
 */
class OC_Hook {
    static private $registered = array();

    /**
     * @brief connects a function to a hook
     * 20140114 新增 index 參數，可以設定 hooks 要被執行的順序
     * @param $signalclass class name of emitter
     * @param $signalname name of signal
     * @param $slotclass class name of slot
     * @param $slotname name of slot
     * @param $index 要被執行的順序
     * @returns true/false
     *
     * This function makes it very easy to connect to use hooks.
     */
    static public function connect($signalclass, $signalname, $slotclass, $slotname, $index = null) {
        # Create the data structure
        if (!array_key_exists($signalclass, self::$registered)) {
            self::$registered[$signalclass] = array();
        }
        if (!array_key_exists($signalname, self::$registered[$signalclass])) {
            self::$registered[$signalclass][$signalname] = array();
        }

        # register hook
        if ($index === null) {
            self::$registered[$signalclass][$signalname][] = array(
                "class" => $slotclass,
                "name" => $slotname
            );
        } else {
            # 指定註冊的順序
            self::$registered[$signalclass][$signalname][$index] = array(
                "class" => $slotclass,
                "name" => $slotname
            );
        }

        # No chance for failure ;-)
        return true;
    }

    /**
     * @brief emitts a signal
     * @param $signalclass class name of emitter
     * @param $signalname name of signal
     * @param $params defautl: array() array with additional data
     * @returns true if slots exists or false if not
     *
     * Emits a signal. To get data from the slot use references!
     *
     */
    static public function emit($signalclass, $signalname, $params = array()) {
        # Return false if there are no slots
        if (!array_key_exists($signalclass, self::$registered)) {
            return false;
        }
        if (!array_key_exists($signalname, self::$registered[$signalclass])) {
            return false;
        }

        # Call all slots
        foreach (self::$registered[$signalclass][$signalname] as $i) {
            call_user_func(array(
                $i["class"],
                $i["name"]
            ), $params);
        }

        # return true
        return true;
    }

}
