{*
    The Kabal Invasion - A web-based 4X space game
    Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    File: faq.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<table>
  <tbody>
  <tr><td class="firstbar">{$langvars['l_faq_title']}</td></tr>
  </tbody></table><br>
<table>
  <tbody>
  <tr>
    <td class="header"><a
      href="faq.php#new">New Players</a>
    <td>
    <td class="header"><a
      href="faq.php#strategies">Strategies</a>
    <td>
    <td class="header"><a
      href="faq.php#misc">Misc</a>
    <td>
    <td class="header"><a
      href="faq.php#qa">Q&amp;A</a>
    <td></td></tr></tbody></table><br>
<table>
  <tbody>
  <tr>
    <td class="header">Introduction: </td></tr>
  <tr>
    <td>
      <p>Welcome to the FAQ for The Kabal Invasion. This most recent
      update occurred on 01.10.2002 and is current for the version 0.3.1. Based on the original FAQ by Garrison.
 </p></td></tr></tbody></table><br>
<table>
  <tbody>
  <tr>
    <td class="header">Table of Contents: </td></tr>
  <tr>
    <td>
      <ol type=I>
        <li><a
        href="faq.php#new">For
        Everyone:</a>
        <ol type=i>
          <li><a
          href="faq.php#new1">New Players
          Guide</a></li>
          <li><a
          href="faq.php#new2">The
          Rules</a></li>
          <li><a
          href="faq.php#new3">More
          Info</a> </li>
        </ol></li></ol>

      <ol type=I start="2"><li><a
        href="faq.php#strategies">Strategies:</a>
        <ol type=i>
          <li><a
          href="faq.php#strategies1">The
          Trader</a></li>
          <li><a
          href="faq.php#strategies2">The
          Builder</a></li>
          <li><a
          href="faq.php#strategies3">The
          Banker</a></li>
          <li><a
          href="faq.php#strategies4">The
          Conqueror</a></li>
          <li><a
          href="faq.php#strategies5">The
          Idiot</a></li></ol></li></ol>

        <ol type=I start="3"><li><a
        href="faq.php#misc">Misc:</a>
        <ol type=i>
          <li><a
          href="faq.php#misc1">Cool
          Tricks</a></li>
          <li><a
          href="faq.php#misc2">Planetary
          Production Values</a></li>
          <li><a
          href="faq.php#misc3">Hull
          Sizes by Tech Level</a></li>
          <li><a
          href="faq.php#misc4">Upgrade
          Costs by Tech Level</a></li>
          <li><a
          href="faq.php#misc5">The
          Particulars of Combat</a></li>
          <li><a
          href="faq.php#misc6">How...
          or How Not to Colonize a Planet</a></li></ol></li></ol>
        <p><a
        href="faq.php#qa">Questions and
        Answers:</a></p>
<br><br></td></tr></tbody></table><br><a id="new"></a>
<table>
  <tbody>
  <tr>
    <td class="header" colSpan=3>For Everyone: </td></tr>
  <tr>
    <td colSpan=3>
      <p>This is the section to read if you are a new player.
      <br><br></p></td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="new1"></a>New Players Guide</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>This will take you to the <a
      href="newplayerguide.php">New Players
      Guide</a>. Click <a href="newplayerguide.php">here</a> to find
      out how to stay alive when you fist log on. <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="new2"></a>The Rules</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>These are the official rules for the game. As this is a web based game
      it's fairly hard to enforce these rules.</p>
      <ol>
        <li>You are not allowed to have multiple accounts. In other words, if
        you have more than one player in the game you are breaking the rules. As
        above, if you need two accounts to test a theory, host your own game. If
        you are caught with multiple accounts you can voluntarily self destruct
        all but one and keep playing that one or lose them all. Remember, the
        admin can always remove your player from the game.</li>
        <li>No inheritance exploits. I have to define this one first. A certain
        player (ok, me) got caught doing this and argued that it wasn't really
        cheating. Pretty much everyone on the forums agreed that he was full of
        crap and I do too. The deal is this. When you're new you get 200 turns.
        You can trade trade trade, leave the money somewhere safe and self
        destruct. Then sign on again with a new account and pick up the money.
        Keep doing this and you get a really high score right off the bat. The
        trick is that everyone knows you're doing this because your score is way
        too high for the low number of turns you have. There are measures being
        taken in the code to prevent this trick from being possible.</li>
        <li>Please, no cursing in the beacons or the forums.</li>
        <li>If you find a bug it is against the rules to exploit it. You must
        report it right away to the webmaster and preferably to the forums as
        well. </li></ol>
      <br><br></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="new3"></a>More Info</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>You are going to have more questions. I say this with a fair amount of
      certainty. There are two really good sources of answers I know of other
      than this FAQ. First, you can always send a message to one of the top 10
      players in the game. They should know the answer, but they may not bother
      to reply. The second source is the <a
      href="http://kabal.tk/forums/">official forum</a>. 
      <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr></tbody></table><br><a id="strategies"></a>
<table>
  <tbody>
  <tr>
    <td class="header" colSpan=3>Strategies: </td></tr>
  <tr>
    <td colSpan=3>
      <p>These are some generic strategies to help you get started. These aren't
      set in stone, and are very general. They should give you an idea of what
      works and what doesn't though. There are of course many more strategies,
      but these are the ones I thought up at 2 in the morning. :)
      <p>Special Note: For all of these strategies I am assuming that you have
      already survived your first couple of days in the game. If you are still a
      newbie, read the <a
      href="newplayerguide.php">New Player
      Guide</a> first. <br><br></p></td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="strategies1"></a>The Trader</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>The Trader primarily spends his time trading. The best thing to do is
      find a goods port and an ore port in adjacent sectors. Trade back and
      forth until either you can afford a hull upgrade or the port's prices are
      no longer very good. Keep doing this. When you're engines are large enough
      to realspace (this varies on the galaxy size in each game, usually
      anywhere from 14 to 18) start doing trade routes between goods and ore
      ports. They don't have to be adjacent at this point. Be sure to buy a fuel
      scoop if you're going to realspace trade (trade route).
      <p>Be sure to have the maximum amount of EWDs and an escape pod at all
      times to ensure survival. You don't have to upgrade any techs except for
      hull, energy, and engines. Everything else is good for combat or
      colonizing. Your military techs can be zero as the EWDs are your primary
      means of survival.
      <p><b>PROS:</B> Quick rise in score. Good to play catch up if you enter
      the game late. <br><b>CONS:</B> Lack of planetary empire means that you'll
      lose out in the long run. I find that the Trader is only effective up to
      about a hull level of 18 or so. That's just my gut reaction. It might be
      wrong. It's probably a lower tech level in reality. <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="strategies2"></a>The Builder</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>The Builder is mainly concerned in building a planetary empire. As
      such, he should build his hull to a level 15-16. Then start colonizing a
      planet. Colonize planets to about 25-50 million before moving on to the
      next planet. The reason for not fully colonizing a planet is that you want
      the colonists to procreate for as long as possible. They stop when there
      are 100 million people on a planet. I guess sex is boring at that point.
      Didn't think that was possible. My bad. :)
      <p>Here's the deal on upgrading. When you hit a 15-16 hull level, upgrade
      everything to within 4 of your hull. Actually, forget about sensors.
      Builders don't need sensors. They don't need amour either for that
      matter. Always have full EWDs and an escape pod. Every time you upgrade
      your hull, upgrade the other techs. Quit upgrading engines when you can
      realspace anywhere in 1 turn. Upgrade as soon as you can.
      <p>So far as colonizing is concerned, realspace to a special port. Pick up
      a full load of colonists, fighters, and torps. Realspace to your new
      planet. Drop off colonists, fighters, torps, and the energy you made from
      realspacing. On each new planet, set the energy production to 5% and all
      other productions to zero. You'll need the energy to power planetary
      shields and beams.
      <p>Colonize constantly. Use the money made by your planets to buy the
      stuff to supply your planets. You don't really need to trade much in this
      strategy.
      <p><b>PROS:</B> You make lots of cash in the long run. <br><b>CONS:</B>
      Kind of slow to start. Conquerors can sometimes take your planets.
      <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="strategies3"></a>The Banker</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>The Banker builds one planet to full
      capacity. Upgrade as though you were a builder. Be sure that the planet is
      completely well defended. Keep adding fighters. If you think that the
      planet has a ridiculously high number of fighters, then it's probably the
      right number. I'd recommend spending something like 5-10% of your turns
      adding more fighters and torps to the planet.
      <p>Ok, here's the way the Banker makes his living. Put all your money on
      the planet and then land on the planet. It should be well defended enough
      to survive any attacks. Wait 600 turns, during which the money will earn
      interest. Play the 600 turns as though you were a Trader. At the end, put
      the new money on the planet and wait another 600 turns before you play
      again. The important thing is to let the money sit around and accrue
      interest for as long as possible.
      <p>This strategy works fairly well if you combine it with a Builder, i.e..
      Build a bunch of planets, but Bank on one of them. Harder to defend your
      empire this way.
      <p><b>PROS:</B> You can make a metric buttload of cash if you're patient.
      <br><b>CONS:</B> You can only play every couple of days and you don't have
      many planets to produce for you. <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="strategies4"></a>The Conqueror</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>The premise here is that you build up your military techs (shields,
      armor, computers, torps, and to a lesser degree sensors) and use them to
      take other people's planets. You then use the money acquired from these
      new planets to upgrade even further. You end up with lots of ill gotten
      colonists and planets this way. They will make money for you and you will
      gain an empire similar to that a Builder might create.
      <p>Be sure to stock every new planet acquired with plenty of fighters and
      torps to be sure that the former owner won't come and try to take the
      planet back. Trust me, that sucks.
      <p><b>PROS:</B> You can get a whole lot of colonists using a small number
      of turns. <br><b>CONS:</B> Everyone will hate you and it's sometimes hard
      to defend new "acquisitions". <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="strategies5"></a>The Idiot</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>This is more a list of what not to do. I've seen people do things that
      boggle the mind, but the truth is that they just don't know any better.
      Here's a listing. Don't...
      <ol>
        <li>...scan over and over and over. It is a waste of turns. Don't scan
        ships or planets unless you actively plan on attacking them. For one,
        it's a waste of turns. Also, it pisses people off to be scanned. You
        will be marked if you scan someone more than once.
        <li>...waste your time trading energy or organics. Ore and goods will
        give you the greatest returns.
        <li>...realspace move unless you can get somewhere in 1 or 2 turns. I've
        seen people use 50-100 turns to move from where they are to sol. You
        could probably move from sector to sector and find a special port using
        less turns. Plus you might find other planets or trading ports along the
        way. </li></ol>
      <p>If I think of more, or you <a href="mailto:wallkk@udel.edu">email
      me</a>, I'll add them to this list. <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr></tbody></table><br><a id="misc"></a>
<table>
  <tbody>
  <tr>
    <td class="header" colSpan=3>Misc: </td></tr>
  <tr>
    <td colSpan=3>
      <p>This section contains some information on game mechanics. For example,
      if you want to know how many units of organics your planet can make in a
      turn, you could probably find it here. <br><br></p></td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="misc1"></a>Cool Tricks</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>Please send me any cool tricks you think of. I'll list the ones I know.

      <ol>
        <li>Before you attack a planet check to see if it is set to sell. If it
        is, buy all the energy. The planet's beams and shields will be made
        useless.
        <li>If you need to go to a special port and don't particularly care
        where you go afterwards, shop at good old sector 0. After you're done
        wait around and let the update that runs every 6 minutes place you in a
        random sector. You effectively get a free move. Of course, this only
        works if you're hull is over the allowed federation space limit. I call
        this the "sol bump".
        <li>Whenever you buy an EWD, buy a warp editor to go along with it. That
        way, if you get attacked you can easily create a link back to wherever
        you were before. If being there is important that is.
</li></ol><br><br></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="misc2"></a>Planetary Production Values</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>Planets can produce all sorts of things. Here goes. By the way, all of
      the commodity production rates assume you're producing at 100% capacity.
      If you're putting 50% towards a commodity, for example, it would take
      twice as many colonists to make the same number of said items.
      <ol>
        <li>Colonists reproduce at the rate of 1.0005 * your current population
        each turn. So, it takes 2000 colonists to have one baby each turn. Man,
        these guys need some more mood music I guess.
        <li>Money left on the planet will increase at the rate of 1.0005 *
        current money on planet each turn. In other words, you get .05% interest
        on your cash every turn. This works out pretty well if you have lots of
        money.
        <li>It takes 20,000 colonists to make 1 fighter each turn. The number of
        fighters produced is as follows: number of colonists * .005 * .01
        <li>It takes 20,000 colonists to make 1 torpedo each turn. The number of
        torps produced is as follows: number of colonists * .005 * .01
        <li>It takes 800 colonists to make 1 unit of ore each turn. The number
        of ore units produced is as follows: number of colonists * .005 * .25
        <li>It takes 400 colonists to make 1 unit of organics each turn. The
        number of organics units produced is as follows: number of colonists *
        .005 * .50
        <li>It takes 800 colonists to make 1 unit of goods each turn. The number
        of goods units produced is as follows: number of colonists * .005 * .25
        <li>It takes 2000 colonists to make 1 unit of energy each turn. The
        number of energy units produced is as follows: number of colonists *
        .005 * .1
        <li>It takes 67 colonists to make 1 credit each turn. The number of
        credits produced is as follows: number of colonists * .005 * 3.0
      </li></ol><br><br></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="misc3"></a>Hull Sizes by Tech Level</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>This list will show you how many items you can store in your holds.
      This list also represents armor, shields, beams, torps, and fighter
      capacity. I show up to tech level 24 That's the highest I've ever gotten
      to. The formula for figuring out the capacity of your holds or other tech
      levels you
      will have is 100*(1.5^(the tech level in question)). Round off that number
      and you have your answer.</p><br><br>
      <table style="width:100%">
        <tbody>
        <tr>
          <td class="lists">
            <ol start=0>
              <li>100
              <li>150
              <li>225
              <li>338
              <li>506 </li></ol></td>
          <td class="lists">
            <ol start=5>
              <li>759
              <li>1,138
              <li>1,709
              <li>2,563
              <li>3,844 </li></ol></td>
          <td class="lists">
            <ol start=10>
              <li>5,767
              <li>8,650
              <li>12,975
              <li>19,462
              <li>29,193 </li></ol></td>
          <td class="lists">
            <ol start=15>
              <li>43,789
              <li>65,684
              <li>98,526
              <li>147,789
              <li>221,684 </li></ol></td>
          <td class="lists">
            <ol start=20>
              <li>332,526
              <li>498,789
              <li>748,182
              <li>1,122,274
              <li>1,683,411 </li></ol></td></tr></tbody></table><br><br></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="misc4"></a>Upgrade Costs by Tech Level</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>This is a listing of how much it costs to upgrade to a certain tech level.
      For example, upgrading from 0 to 1 costs 1,000 credits.</p><br><br>
      <table style="width:100%">
        <tbody>
        <tr>
          <td class="lists">
            <ol start=1>
              <li>1,000
              <li>2,000
              <li>4,000
              <li>8,000
              <li>16,000 </li></ol></td>
          <td class="lists">
            <ol start=6>
              <li>32,000
              <li>64,000
              <li>128,000
              <li>256,000
              <li>512,000 </li></ol></td>
          <td class="lists">
            <ol start=11>
              <li>1,024,000
              <li>2,048,000
              <li>4,096,000
              <li>8,192,000
              <li>16,384,000 </li></ol></td>
          <td class="lists">
            <ol start=16>
              <li>32,768,000
              <li>65,536,000
              <li>131,072,000
              <li>262,144,000
              <li>524,288,000 </li></ol></td>
          <td class="lists">
            <ol start=21>
              <li>1,048,576,000
              <li>2,097,152,000
              <li>4,194,304,000
              <li>8,388,608,000
              <li>16,777,216,000 </li></ol></td></tr></tbody></table><br><br></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="misc5"></a>The Particulars of Combat</td>
    <td class="spacer">&nbsp;</td>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>Combat is tricky. I'm going to lay it out one step at a time. First
      we'll do ship to ship combat, and then we'll do ship to planet combat.
      This is basically an English representation of the code. Now, those of you
      who complain about not being able to read the PHP code can quiet down.
      <p>One very important thing that I want to stress is this. In a fight, you
      use 100% of your fighters. You only use 2% of your torps. This is because
      the torpedo launchers mounted on your ship can only launch a salvo equal
      to 2% of the maximum torps you can carry. Make sense? I hope so.
      <p><b>VERY IMPORTANT ADDITION TO VERSION 0.1.14 COMBAT:</B><br>Combat now
      requires energy for beams and shields. If you have 20k energy on your ship
      and your beams can support 25k beams and your shields are at 25k shields,
      you'll actually get 20k beams and 0 shields. The reason is that beams use
      energy before shields do. In this example, you'd have to have 50k energy
      on hand for beams and shields to both be at max power.
      <p><b>Ship to Ship Combat</B>
      <p>Ship to ship combat happens in a very straightforward manner. Here we
      go. I'll be using the following method to determine who is the attacker
      and who is the defender. a_shields is attacker shields. d_shields is
      defender shields. No here we go.
      <ol>
        <li>First, a_engines and d_engines are compared. A chance to attack is
        determined by this formula: success=(10-d_engines+a_engines)*5. This
        number is then compared to a random number between 1 and 100. If the
        random number is higher than the success number, the attack goes on.
        Otherwise you get a message saying "Target out maneuvered
        you!".<br><br>Here's an example. If your engines are 13 and his engines
        are 16, then we calculate the success rate as (10-16+13)*5. The result
        is 35. Hence, you have a 65% chance (35% chance to fail) to
        succeed.<br><br>
        <li>Second, a_sensors and d_cloak are compared. A chance to attack is
        determined by this formula: success=(10-d_cloak+a_sensors)*5. This
        number is then compared to a random number between 1 and 100. If the
        random number is lower than the success number, the attack goes on.
        Otherwise you get a message saying "Unable to get a lock on
        target!".<br><br>Here's an example. If your sensors are 7 and his cloak
        is 3, then we calculate the success rate as (10-3+7)*5. This result is
        70. This means you have a straight 70% chance of success.<br><br>I know
        this looks the same as the above engines check, but here you get the
        success percent right away. There you have to subtract from 100. Looks
        like two different people wrote this code. Incidentally, there's always
        at least a 5% success or 5% failure chance. Nothing is certain.<br><br>
        <li>Okay, now combat is a go. If the defender has an Emergency Warp
        Device, it is used and the defender is sent to a random sector between 1
        and the max sector number, which is 5000 in this game. Combat, of
        course, would end. If the defender has no Emergency Warp Devices, combat
        is continued.<br><br>
        <li>First, beams are exchanged against fighters. The a_beams will
        destroy up to half of the d_fighters and vice versa.<br><br>For example,
        you have 20,000 beams and he has 14,000 fighters. Your beams will take
        out 7,000 fighters (half) and leave you with 13,000 beams left over. If
        you had 20,000 beams and he had 47,000 fighters, you would take out
        20,000 fighters. That would leave you with 0 beams and leave him with
        27,000 fighters.<br><br>
        <li>This step only happens if either player has any beams left. Assume
        we have beams left. The a_beams will go against d_shields. If the beams
        are higher, they will negate all of the shields and there will still be
        some beams left over. The same thing goes for the defender's beams
        against your shields.<br><br>For example, you have 7,000 beams left over
        from the previous step. Your opponent has 20,000 shields. Your beams
        would take away 7,000 shield points and your beams would be done. If he
        had had only 6,000 shields, your beams would have taken away all shields
        and left you with 1,000 beams left over.<br><br>
        <li>This step also only happens if there are beams left over from the
        previous two steps. In this step, a_beams are matched up against
        d_armor. If your beams are greater than his armor, then he is going to
        die. If your beams aren't high enough, you just take away that many
        points of armor.<br><br>For example, you have 3,000 beams left over and
        the opponent has 40,000 armor. You'll take away 3,000, leaving him with
        37,000 armor. If he had 3,000 armor or less, he would die in the
        conflict. Death equates to an armor rating of 0 or less.<br><br>
        <li>Now we have an exchange of torpedoes. In this version of the game
        torps have a damage rating of 10. This is
        something that can be changed in the config file, so it might not always
        be the same. First off, torp damage is calculated by multiplying the
        number of torps you have by the torp damage rate. So, if you had 400
        torps, your torp damage would be 4,000 (400*10).<br><br>If the defender
        has any fighters left, the torp damage will take out up to half of them.
        It's basically the same as with the beams. So, if your torp damage is
        4,000 and d_fighters is 5,000, you will take out 2,500 fighters. That'll
        leave you with 1,500 worth of torp damage to work with. If he had had
        10,000 fighters, you would have taken out a full 4,000 of them. You
        wouldn't have any torp damage left though.<br><br>
        <li>If you have any torp damage left, it is applied to the defender's
        armor. So, if you had 4,000 torp damage left, you'd take away 4,000
        armor.<br><br>
        <li>Now, fighters attack. Your original fighters total is subtracted from
        his fighter total, and his original total is deleted from yours. This
        might not seem immediately intuitive, but it is. I'll give some
        examples.<br><br>You have 40,000 and he has 36,000. You'll end up with
        4,000 left over and he'll end up with 0.<br>You have 20,000 and he has
        20,000. You'll both end up with 0.<br>You have 15,000 and he has 27,000.
        You'll end up with 0 and he'll end up with 12,000.<br><br>
        <li>If there are any fighters left, they are applied to the defender's
        armor. So, if you have 34,000 fighters left, you can do 34,000 damage to
        d_armor. If the defender doesn't have enough armor left, too bad.<br><br>
        <li>The last step is to test whether or not either player is dead. If
        either player has armor of 0 or less, they are dead. If you die, life
        sucks. You learned a hard lesson. If your opponent dies and you live,
        you get some money based on salvaging his ship. If you want to know how
        much, look in the code. I'm tired. </li></ol>
      <p><b>Ship to Planet Combat</B>
      <p>This works almost exactly the same as above. If the defender's ship is
      not on the planet, then the planet is considered defeated if its shields
      and fighters are reduced to 0. The planet has no armor, so skip the part
      where you attack the opponent's armor.
      <p>If the planet's owner is on the planet, then things are somewhat more
      complicated. You should understand how combat works from the above
      listing, so I'll just list the order in which things happen.
      <ol>
        <li>Your beams can take out up to half of the planet's fighters.
        <li>Planet beams take out up to half of your fighters.
        <li>Owner beams take out up to half of your fighters.
        <li>Player beams go against planet shields.
        <li>Planet beams go against your shields.
        <li>Owner beams go against your shields.
        <li>Your beams go against owner armor.
        <li>Planet beams go against your armor.
        <li>Owner beams go against your armor.
        <li>Your torp damage takes out planet fighters.
        <li>Your torp damage takes out up to half of the owner's fighter.
        <li>Planet torps take out up to half of your fighters.
        <li>Owner torps take out up to half of your fighters.
        <li>Your torp damage goes against owner's armor.
        <li>Planet torp damage goes against your armor.
        <li>Owner torp damage goes against your armor.
        <li>Your fighters go against planet fighters.
        <li>Your fighters go against owner fighters.
        <li>Your fighters go against planet shields.
        <li>Your fighters go against owner armor.
        <li>Planet fighters go against your armor.
        <li>Owner fighters go against your armor.
        <li>If your armor is 0 or less, you die. Bozo.
        <li>If owner armor is 0 or less, he dies. Good job.
        <li>If you're alive, he's dead, and the planet has no fighters or
        shields, you win and get the planet. Well played. </li></ol>
      <p>See, I told you it was easy. <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="subheader"><a id="misc6"></a>How... or How Not to Colonize a Planet</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p>This is something I wrote in the forums... thought it belonged here.
      Standard cut 'n' paste action. Here goes:
      <p>Also, I've seen planets that have 100 million colonists on them
      already. I'm gonna give a short lesson right now on why you should NEVER
      colonize a planet to 100 million people.
      <p>First, I pose a question. How many extra, and conveniently free,
      colonists does a maxed out planet make per turn. The answer is zero.
      <p>Now, how many new colonists could be produced by 100 million peeps if
      they were allowed to reproduce. The answer is 50k peeps. At 5 creds per
      peep, that's a value of 250k per turn for free. You don't even have to
      transport them from a special. They take care of that on their own.
      <p>Now imagine that you don't have 100 million peeps on one planet, but 50
      million each on two planets. Now, each planet will make 25k peeps per turn
      and you'll get your 250k credits worth between the two planets.
      <p>Those two planets will take exactly 1387 turns to reproduce until they
      are full. You will gain free colonists, which means free money, on every
      one of those turns. If you had just the one planet, you'd get nothing for
      free.
      <p>Now imagine that you had spread those colonists over four planets
      instead of two. It would take each of those four planets 2774 turns to go
      from 25 million peeps to 100 million peeps. You'd be getting free people
      for that many turns. Ultimately you'll get an additional 300 million
      people for free. At 5 creds per person that's 1.5 billion credits for
      free. It's spread over 2774 turns, but it's still a damn lot of credits
      for free.
      <p>I assume you see where I'm going with this. Residual income is a gold
      mine. By spreading the same number of colonists over a greater number of
      planets you are increasing the total future amount of residual income. The
      only downside is that you have more planets to defend. The upside is that
      even if you lose one, you have other equally large planets to rely on for
      income.
      <p>Hence, the moral of this story is not to colonize to 100 million. It's
      dumb. I am going to suggest a maximum colonizing limit of 15-25 million
      colonists. That gives you a solid planet, but also gives you plenty of
      time for the planets to grow. That's just a suggestion. Use your own
      judgment.
      <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr></tbody></table><a id="qa"></a>
<table>
  <tbody>
  <tr>
    <td class="header" colSpan=3>Questions and Answers: </td></tr>
  <tr>
    <td colSpan=3>
      <p>When people send me questions, I'll answer them here. I'll reprint the
      question and answer it to the best of my ability. <br><br></p></td></tr>
  <tr>
    <td class="spacer">&nbsp;</td>
    <td class="spacer">&nbsp;</td>
    <td class="spacer">&nbsp;</td></tr>
  <tr>
    <td style="width:5%">&nbsp;</td>
    <td style="width:90%">
      <p> <br><br></p></td>
    <td style="width:5%">&nbsp;</td></tr></tbody></table>
{$variables['linkback']['fulltext']|replace:"[here]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_here']}</a>"}
