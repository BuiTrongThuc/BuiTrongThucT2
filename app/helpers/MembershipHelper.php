<?php
class MembershipHelper
{
    public static function getTierDefinitions()
    {
        return array(
            'bac' => array(
                'name' => 'Bạc',
                'class' => 'tier-silver',
                'min_spent' => 0,
                'benefits' => array(
                    'Tích điểm 1% giá trị đơn hàng',
                    'Voucher sinh nhật 50.000đ',
                    'Hỗ trợ đổi trả trong 3 ngày',
                    'Nhận thông báo khuyến mãi sớm'
                )
            ),
            'vang' => array(
                'name' => 'Vàng',
                'class' => 'tier-gold',
                'min_spent' => 20000000,
                'benefits' => array(
                    'Tích điểm 2% giá trị đơn hàng',
                    'Voucher hằng tháng 100.000đ',
                    'Miễn phí vận chuyển cho đơn từ 2.000.000đ',
                    'Ưu tiên xử lý đơn hàng',
                    'Giảm thêm 3% cho phụ kiện'
                )
            ),
            'kim_cuong' => array(
                'name' => 'Kim cương',
                'class' => 'tier-diamond',
                'min_spent' => 50000000,
                'benefits' => array(
                    'Tích điểm 5% giá trị đơn hàng',
                    'Voucher VIP 300.000đ',
                    'Miễn phí vận chuyển mọi đơn hàng',
                    'Hỗ trợ ưu tiên cấp cao',
                    'Ưu đãi bảo hành/đổi trả nâng cao',
                    'Quà sinh nhật dành riêng cho VIP'
                )
            )
        );
    }

    public static function calculateTier($totalSpent)
    {
        $totalSpent = (float)$totalSpent;
        if ($totalSpent >= 50000000) {
            return 'kim_cuong';
        }
        if ($totalSpent >= 20000000) {
            return 'vang';
        }
        return 'bac';
    }

    public static function getRealmName($level)
    {
        $level = max(1, (int)$level);
        $realms = array(
            1 => 'Luyện Khí tầng 1',
            2 => 'Luyện Khí tầng 2',
            3 => 'Luyện Khí tầng 3',
            4 => 'Trúc Cơ sơ kỳ',
            5 => 'Trúc Cơ trung kỳ',
            6 => 'Trúc Cơ hậu kỳ',
            7 => 'Kim Đan sơ kỳ',
            8 => 'Kim Đan trung kỳ',
            9 => 'Kim Đan hậu kỳ',
            10 => 'Nguyên Anh sơ kỳ',
            11 => 'Nguyên Anh trung kỳ',
            12 => 'Nguyên Anh hậu kỳ',
            13 => 'Hóa Thần',
            14 => 'Luyện Hư',
            15 => 'Đại Thừa'
        );
        return $realms[min($level, 15)] ?? ('Cảnh giới ' . $level);
    }

    public static function getRequiredExp($level)
    {
        $level = max(1, (int)$level);
        return 100 + (($level - 1) * 50);
    }

    public static function getProgress($user)
    {
        $level = max(1, (int)($user->cultivation_level ?? 1));
        $exp = max(0, (int)($user->cultivation_exp ?? 0));
        $required = self::getRequiredExp($level);
        $percent = $required > 0 ? min(100, round($exp / $required * 100)) : 0;
        return array(
            'level' => $level,
            'realm' => self::getRealmName($level),
            'exp' => $exp,
            'required' => $required,
            'percent' => $percent
        );
    }

    public static function addEnergy(PDO $db, $accountId, $amount, $reason, $sourceType = null, $sourceId = null)
    {
        $accountId = (int)$accountId;
        $amount = max(0, (int)$amount);
        if ($accountId <= 0 || $amount <= 0) {
            return null;
        }

        $stmt = $db->prepare('SELECT id, cultivation_exp, cultivation_level FROM account WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => $accountId));
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!$user) {
            return null;
        }

        $level = max(1, (int)($user->cultivation_level ?? 1));
        $exp = max(0, (int)($user->cultivation_exp ?? 0)) + $amount;
        $leveledUp = false;

        while ($exp >= self::getRequiredExp($level)) {
            $exp -= self::getRequiredExp($level);
            $level++;
            $leveledUp = true;
        }

        $upd = $db->prepare('UPDATE account SET cultivation_exp = :exp, cultivation_level = :level WHERE id = :id');
        $upd->execute(array(':exp' => $exp, ':level' => $level, ':id' => $accountId));

        try {
            $log = $db->prepare('INSERT INTO cultivation_logs (account_id, exp_change, reason, source_type, source_id, level_after, exp_after) VALUES (:account_id, :exp_change, :reason, :source_type, :source_id, :level_after, :exp_after)');
            $log->execute(array(
                ':account_id' => $accountId,
                ':exp_change' => $amount,
                ':reason' => $reason,
                ':source_type' => $sourceType,
                ':source_id' => $sourceId,
                ':level_after' => $level,
                ':exp_after' => $exp
            ));
        } catch (Exception $e) {}

        return array(
            'level' => $level,
            'exp' => $exp,
            'required' => self::getRequiredExp($level),
            'realm' => self::getRealmName($level),
            'leveled_up' => $leveledUp
        );
    }

    public static function addSpending(PDO $db, $accountId, $amount)
    {
        $accountId = (int)$accountId;
        $amount = max(0, (float)$amount);
        if ($accountId <= 0 || $amount <= 0) {
            return;
        }

        $stmt = $db->prepare('SELECT total_spent FROM account WHERE id = :id LIMIT 1');
        $stmt->execute(array(':id' => $accountId));
        $oldSpent = (float)$stmt->fetchColumn();
        $newSpent = $oldSpent + $amount;
        $tier = self::calculateTier($newSpent);

        $upd = $db->prepare('UPDATE account SET total_spent = :total_spent, member_tier = :member_tier WHERE id = :id');
        $upd->execute(array(':total_spent' => $newSpent, ':member_tier' => $tier, ':id' => $accountId));
    }
}
?>
