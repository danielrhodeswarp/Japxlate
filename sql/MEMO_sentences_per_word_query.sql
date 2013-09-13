-- in order of decreasing goodness

SELECT char_length(concat(sent.sentence, tran.sentence)) as lenf, sentences_per_word.checked, sent.sentence, tran.sentence FROM sentence as sent, sentence as tran, sentences_per_word,
edict 
where

sent.id = sentences_per_word.sentence_id and tran.id = sentences_per_word.meaning_id

and (sentences_per_word.kanji = edict.kanji or sentences_per_word.kana = edict.kana)
-- and sentences_per_word.kana = edict.kana

and edict.kanji = '換える' and edict.kana = 'かえる'

-- and sent.sentence like '%換%'

-- and sentences_per_word.checked = 'y'
-- and char_length(concat(sent.sentence, tran.sentence)) < 140


  LIMIT 0,10